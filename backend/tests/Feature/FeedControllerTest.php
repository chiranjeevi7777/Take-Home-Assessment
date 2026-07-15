<?php

namespace Tests\Feature;

use App\Contracts\EmbeddingProvider;
use App\Models\Post;
use App\Models\User;
use App\Models\Relationship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedControllerTest extends TestCase
{
    use RefreshDatabase;

    private $embeddingProviderMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->embeddingProviderMock = $this->createMock(EmbeddingProvider::class);
        $this->app->instance(EmbeddingProvider::class, $this->embeddingProviderMock);

        config([
            'ranking.weights' => [
                'authenticity' => 0.30,
                'relationship' => 0.25,
                'similarity' => 0.25,
                'recency' => 0.20,
            ],
            'ranking.decay_rate' => 0.05,
            'ranking.default_similarity' => 0.5,
            'ranking.candidate_pool_size' => 10,
        ]);
    }

    /** @test */
    public function it_returns_ranked_posts_for_the_viewer()
    {
        $viewer = User::factory()->create();
        $author = User::factory()->create(['authenticity_score' => 90.0]);

        // Establish relationship
        Relationship::create([
            'follower_id' => $viewer->id,
            'following_id' => $author->id,
            'strength' => 5.0, // Mid strength (5/10) -> 0.5 normalized
        ]);

        // Create some posts
        $post1 = Post::create([
            'user_id' => $author->id,
            'content' => 'High quality post number one.',
            'authenticity_score' => 90.0,
        ]);

        $post2 = Post::create([
            'user_id' => $author->id,
            'content' => 'High quality post number two.',
            'authenticity_score' => 90.0,
        ]);

        // Mock similarity scores from the embedding provider
        $this->embeddingProviderMock->expects($this->any())
            ->method('getSimilarityScores')
            ->willReturn([
                $post1->id => 0.8,
                $post2->id => 0.4,
            ]);

        $response = $this->actingAs($viewer)
            ->getJson('/api/feed');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'user',
                        'authenticity_score',
                        'ranking_score',
                        'reactions_count',
                        'user_reaction',
                        'created_at',
                    ],
                ],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);

        // Ensure post1 (higher similarity) is ranked above post2
        $data = $response->json('data');
        $this->assertCount(2, $data);
        $this->assertEquals($post1->id, $data[0]['id']);
        $this->assertEquals($post2->id, $data[1]['id']);
    }
}
