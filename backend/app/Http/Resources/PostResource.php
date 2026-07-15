<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Additional data merged into the resource.
     * Used to inject ranking_score and user_reaction from the service layer.
     */
    private ?float $rankingScore = null;
    private ?string $userReaction = null;

    public function withRankingScore(?float $score): static
    {
        $this->rankingScore = $score;
        return $this;
    }

    public function withUserReaction(?string $reaction): static
    {
        $this->userReaction = $reaction;
        return $this;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'authenticity_score' => (float) $this->user->authenticity_score,
            ],
            'authenticity_score' => (float) $this->authenticity_score,
            'ranking_score' => $this->rankingScore,
            'reactions_count' => $this->interactions_count ?? $this->interactions()->count(),
            'user_reaction' => $this->userReaction,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
