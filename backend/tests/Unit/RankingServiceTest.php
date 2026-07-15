<?php

namespace Tests\Unit;

use App\Contracts\EmbeddingProvider;
use App\Services\RankingService;
use App\Services\Scoring\AuthenticityScorer;
use App\Services\Scoring\RecencyScorer;
use App\Services\Scoring\RelationshipScorer;
use App\Services\Scoring\SimilarityScorer;
use Tests\TestCase;

class RankingServiceTest extends TestCase
{
    private RankingService $rankingService;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'ranking.weights' => [
                'authenticity' => 0.30,
                'relationship' => 0.25,
                'similarity' => 0.25,
                'recency' => 0.20,
            ],
            'ranking.decay_rate' => 0.05,
            'ranking.default_similarity' => 0.5,
        ]);

        $embeddingProvider = $this->createMock(EmbeddingProvider::class);

        $this->rankingService = new RankingService(
            new AuthenticityScorer(),
            new RelationshipScorer(),
            new SimilarityScorer($embeddingProvider),
            new RecencyScorer()
        );
    }

    /** @test */
    public function it_computes_composite_weighted_score_correctly()
    {
        $signals = [
            'authenticity' => 0.8, // 0.8 * 0.3 = 0.24
            'relationship' => 0.6, // 0.6 * 0.25 = 0.15
            'similarity' => 0.7,   // 0.7 * 0.25 = 0.175
            'recency' => 0.9,      // 0.9 * 0.20 = 0.18
        ];

        // Total expected = 0.24 + 0.15 + 0.175 + 0.18 = 0.745
        $score = $this->rankingService->computeScore($signals);
        $this->assertEquals(0.745, $score);
    }

    /** @test */
    public function it_provides_score_explanations()
    {
        $signals = [
            'authenticity' => 0.8,
            'relationship' => 0.6,
            'similarity' => 0.7,
            'recency' => 0.9,
        ];

        $explanation = $this->rankingService->explain($signals);

        $this->assertEquals(0.745, $explanation['total']);
        $this->assertEquals(0.30, $explanation['authenticity']['weight']);
        $this->assertEquals(0.8, $explanation['authenticity']['raw_value']);
        $this->assertEquals(0.24, $explanation['authenticity']['contribution']);
    }
}
