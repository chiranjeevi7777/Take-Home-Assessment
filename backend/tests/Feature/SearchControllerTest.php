<?php

namespace Tests\Feature;

use App\Contracts\EmbeddingProvider;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchControllerTest extends TestCase
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
    public function it_returns_semantic_search_results_via_provider()
    {
        $viewer = User::factory()->create();
        $author = User::factory()->create();

        $post1 = Post::create(['user_id' => $author->id, 'content' => 'Laravels semantic search features.', 'authenticity_score' => 80]);
        $post2 = Post::create(['user_id' => $author->id, 'content' => 'Something unrelated.', 'authenticity_score' => 80]);

        // Mock semantic search search
        $this->embeddingProviderMock->expects($this->once())
            ->method('search')
            ->with('laravel', 40)
            ->willReturn([
                $post1->id => 0.85,
            ]);

        $response = $this->actingAs($viewer)
            ->getJson('/api/search?q=laravel');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($post1->id, $data[0]['id']);
    }

    /** @test */
    public function it_falls_back_to_like_search_on_provider_failure()
    {
        $viewer = User::factory()->create();
        $author = User::factory()->create();

        $post = Post::create(['user_id' => $author->id, 'content' => 'Fall back to like search.', 'authenticity_score' => 80]);

        // Mock semantic search failure throwing an exception
        $this->embeddingProviderMock->expects($this->once())
            ->method('search')
            ->willThrowException(new \RuntimeException('Service unavailable'));

        $response = $this->actingAs($viewer)
            ->getJson('/api/search?q=like');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($post->id, $data[0]['id']);
    }
}
