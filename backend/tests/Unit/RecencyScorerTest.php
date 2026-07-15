<?php

namespace Tests\Unit;

use App\Services\Scoring\RecencyScorer;
use Carbon\Carbon;
use Tests\TestCase;

class RecencyScorerTest extends TestCase
{
    private RecencyScorer $scorer;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup configuration mock for decay_rate
        config(['ranking.decay_rate' => 0.05]);
        Carbon::setTestNow(Carbon::now());
        $this->scorer = new RecencyScorer();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** @test */
    public function it_scores_fresh_posts_at_maximum_recency()
    {
        $now = Carbon::now();
        $score = $this->scorer->score($now);
        $this->assertEquals(1.0, $score);
    }

    /** @test */
    public function it_decays_score_exponentially_over_time()
    {
        config(['ranking.decay_rate' => 0.05]);
        $this->scorer = new RecencyScorer();

        // 6 hours ago -> exp(-0.05 * 6) = 0.7408
        $sixHoursAgo = Carbon::now()->subHours(6);
        $score = $this->scorer->score($sixHoursAgo);
        $this->assertLessThan(0.75, $score);
        $this->assertGreaterThan(0.73, $score);

        // 24 hours ago -> exp(-0.05 * 24) = 0.3011
        $oneDayAgo = Carbon::now()->subDays(1);
        $score = $this->scorer->score($oneDayAgo);
        $this->assertLessThan(0.31, $score);
        $this->assertGreaterThan(0.29, $score);
    }

    /** @test */
    public function it_computes_batch_recency_scores()
    {
        $now = Carbon::now();
        $sixHoursAgo = Carbon::now()->subHours(6);

        $timestamps = [
            1 => $now,
            2 => $sixHoursAgo,
        ];

        $scores = $this->scorer->batchScore($timestamps);

        $this->assertCount(2, $scores);
        $this->assertEquals(1.0, $scores[1]);
        $this->assertLessThan(0.75, $scores[2]);
    }
}
