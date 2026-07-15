<?php

namespace Tests\Feature;

use App\Contracts\EmbeddingProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    private $embeddingProviderMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->embeddingProviderMock = $this->createMock(EmbeddingProvider::class);
        $this->app->instance(EmbeddingProvider::class, $this->embeddingProviderMock);
    }

    /** @test */
    public function authenticated_user_can_create_post_and_trigger_embedding()
    {
        $user = User::factory()->create(['authenticity_score' => 85.0]);

        $this->embeddingProviderMock->expects($this->once())
            ->method('embedPost')
            ->willReturn('emb-12345');

        $response = $this->actingAs($user)
            ->postJson('/api/posts', [
                'content' => 'This is a genuine, high-quality post of significant length to trigger all positive heuristics.',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'content',
                    'authenticity_score',
                    'user' => ['id', 'name'],
                ],
            ]);

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'content' => 'This is a genuine, high-quality post of significant length to trigger all positive heuristics.',
            'embedding_id' => 'emb-12345',
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_post()
    {
        $response = $this->postJson('/api/posts', [
            'content' => 'Some content.',
        ]);

        $response->assertStatus(401);
    }
}
