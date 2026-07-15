<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * Authentic-sounding social media post content.
     */
    private const SAMPLE_POSTS = [
        'Just had a real conversation with a stranger at the coffee shop. No phones, no distractions. We need more of this.',
        'Unpopular opinion: vulnerability is not weakness. Sharing your struggles takes more courage than curating a highlight reel.',
        'Three months into learning guitar. I sound terrible but I am loving every minute. Progress over perfection.',
        'Deleted my other social media apps last week. My attention span is slowly recovering.',
        'Hot take: most productivity advice is just anxiety repackaged as hustle culture.',
        'My grandmother taught me that listening is the most underrated skill. She was right about everything.',
        'Tried a 24-hour digital detox. The first hour was uncomfortable. The other 23 were liberating.',
        'Read an actual physical book today. The texture of pages hits different than a screen.',
        'Started volunteering at the local food bank. Perspective is the best antidote to entitlement.',
        'Genuine question: when did we start performing our lives instead of living them?',
        'I failed at my startup. Lost money. Lost time. Gained wisdom I could not have bought.',
        'Morning walks without earbuds. Just birdsong and my own thoughts. Revolutionary concept apparently.',
        'My 5-year-old asked me why adults are always looking at their phones. I did not have a good answer.',
        'Cooking a meal from scratch is meditative. The process matters as much as the result.',
        'Had a disagreement with a friend today. We talked it through like adults. Still friends. Imagine that.',
        'Authenticity is not about sharing everything. It is about not faking anything.',
        'Spent the weekend building a bookshelf with my dad. No tutorial needed. Just decades of his experience.',
        'The best conversations happen when you ask follow-up questions instead of waiting for your turn to talk.',
        'Journaling for 30 days straight. Turns out I had a lot of unprocessed thoughts.',
        'Reminder that comparison is the thief of joy. Your chapter one is not someone else chapter twenty.',
    ];

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'content' => $this->faker->randomElement(self::SAMPLE_POSTS),
            'embedding_id' => null,
            'authenticity_score' => $this->faker->randomFloat(2, 30, 95),
        ];
    }

    /**
     * State: post has been embedded in vector store.
     */
    public function embedded(string $embeddingId = null): static
    {
        return $this->state(fn () => [
            'embedding_id' => $embeddingId ?? 'emb_' . $this->faker->uuid(),
        ]);
    }
}
