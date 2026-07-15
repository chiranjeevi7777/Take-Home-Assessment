<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Models\Interaction;
use App\Models\Relationship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InteractionControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_react_to_a_post_and_strengthen_relationship()
    {
        $viewer = User::factory()->create();
        $author = User::factory()->create();

        $post = Post::create([
            'user_id' => $author->id,
            'content' => 'Authentic content from author.',
            'authenticity_score' => 80.0,
        ]);

        // Send a like reaction
        $response = $this->actingAs($viewer)
            ->postJson('/api/interactions', [
                'post_id' => $post->id,
                'type' => 'like',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'post_id' => $post->id,
                    'type' => 'like',
                    'action' => 'created',
                ],
            ]);

        // Assert database interaction recorded
        $this->assertDatabaseHas('interactions', [
            'user_id' => $viewer->id,
            'post_id' => $post->id,
            'type' => 'like',
        ]);

        // Assert relationship was created and strength incremented
        $this->assertDatabaseHas('relationships', [
            'follower_id' => $viewer->id,
            'following_id' => $author->id,
            'strength' => 0.5, // default increment
        ]);
    }

    /** @test */
    public function a_user_can_toggle_reaction_off()
    {
        $viewer = User::factory()->create();
        $author = User::factory()->create();

        $post = Post::create([
            'user_id' => $author->id,
            'content' => 'Authentic content from author.',
            'authenticity_score' => 80.0,
        ]);

        // Create pre-existing reaction
        Interaction::create([
            'user_id' => $viewer->id,
            'post_id' => $post->id,
            'type' => 'like',
        ]);

        // Toggle reaction off by sending it again
        $response = $this->actingAs($viewer)
            ->postJson('/api/interactions', [
                'post_id' => $post->id,
                'type' => 'like',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'post_id' => $post->id,
                    'type' => 'like',
                    'action' => 'removed',
                ],
            ]);

        $this->assertDatabaseMissing('interactions', [
            'user_id' => $viewer->id,
            'post_id' => $post->id,
            'type' => 'like',
        ]);
    }
}
