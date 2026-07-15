<?php

namespace Tests\Unit;

use App\Services\Scoring\AuthenticityScorer;
use Tests\TestCase;

class AuthenticityScorerTest extends TestCase
{
    private AuthenticityScorer $scorer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scorer = new AuthenticityScorer();
    }

    /** @test */
    public function it_scores_authenticity_correctly_with_length_bonus()
    {
        // Short content (< 50 chars), no bonus
        $shortContent = "One one one one one.";
        $score = $this->scorer->computeRawScore(80.0, $shortContent);
        $this->assertEquals(80.0, $score);

        // Long content (> 50 chars), +3 bonus
        $longContent = "One one one one one one one one one one one one one one one one one one one one.";
        $score = $this->scorer->computeRawScore(80.0, $longContent);
        $this->assertEquals(83.0, $score);
    }

    /** @test */
    public function it_scores_authenticity_correctly_with_diversity_bonus()
    {
        // High word diversity (ratio > 0.7)
        $diverseContent = "One two three four five six seven eight nine ten.";
        $score = $this->scorer->computeRawScore(80.0, $diverseContent);
        $this->assertEquals(82.0, $score); // +2 diversity bonus
    }

    /** @test */
    public function it_applies_caps_penalty_correctly()
    {
        // Excess capitalization penalty (-5)
        $shoutingContent = "THIS IS SHOUTING AND IS VERY CLICKBAITY INDEED PLEASE DO NOT DO THIS";
        $score = $this->scorer->computeRawScore(80.0, $shoutingContent);
        // Long content (+3), shouting (-5), diversity is high (+2) -> 80.0
        // Wait, let's check calculations: base 80 + 3 (length > 50) + 2 (diverse) - 5 (caps) = 80
        $this->assertEquals(80.0, $score);
    }

    /** @test */
    public function it_caps_raw_score_at_one_hundred()
    {
        $content = "This is a very long and detailed post that definitely exceeds fifty characters to get the length bonus.";
        $score = $this->scorer->computeRawScore(99.0, $content);
        $this->assertEquals(100.0, $score);
    }

    /** @test */
    public function it_normalizes_authenticity_score_between_zero_and_one()
    {
        $content = "One one one one one.";
        $normalizedScore = $this->scorer->score(80.0, $content);
        $this->assertEquals(0.8, $normalizedScore);
    }
}
