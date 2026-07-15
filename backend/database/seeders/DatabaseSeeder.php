<?php

namespace Database\Seeders;

use App\Models\Interaction;
use App\Models\Post;
use App\Models\Relationship;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Demo Users ─────────────────────────────────────
        $users = $this->createDemoUsers();

        // ── Posts (5-8 per user) ────────────────────────────
        $allPosts = collect();
        foreach ($users as $user) {
            $posts = Post::factory()
                ->count(rand(5, 8))
                ->for($user)
                ->create([
                    'created_at' => now()->subHours(rand(1, 120)),
                ]);
            $allPosts = $allPosts->merge($posts);
        }

        // ── Relationships (follow graph) ───────────────────
        $this->createRelationships($users);

        // ── Interactions (reactions on posts) ───────────────
        $this->createInteractions($users, $allPosts);
    }

    private function createDemoUsers(): array
    {
        $demoUser = User::create([
            'name' => 'Demo User',
            'email' => 'demo@guisedup.com',
            'password' => Hash::make('password'),
            'authenticity_score' => 78.50,
        ]);

        $users = [$demoUser];

        $profiles = [
            ['name' => 'Maya Chen',     'authenticity_score' => 92.30],
            ['name' => 'James Wright',  'authenticity_score' => 85.10],
            ['name' => 'Sofia Rivera',  'authenticity_score' => 71.80],
            ['name' => 'Liam O\'Brien', 'authenticity_score' => 64.50],
            ['name' => 'Aisha Patel',   'authenticity_score' => 88.90],
            ['name' => 'Kenji Tanaka',  'authenticity_score' => 56.20],
            ['name' => 'Emma Larsson',  'authenticity_score' => 79.40],
            ['name' => 'Carlos Mendez', 'authenticity_score' => 43.60],
            ['name' => 'Zara Williams', 'authenticity_score' => 91.00],
        ];

        foreach ($profiles as $profile) {
            $users[] = User::create([
                'name' => $profile['name'],
                'email' => strtolower(str_replace([' ', "'"], ['.', ''], $profile['name'])) . '@example.com',
                'password' => Hash::make('password'),
                'authenticity_score' => $profile['authenticity_score'],
            ]);
        }

        return $users;
    }

    private function createRelationships(array $users): void
    {
        // Demo user follows everyone
        $demoUser = $users[0];
        for ($i = 1; $i < count($users); $i++) {
            Relationship::create([
                'follower_id' => $demoUser->id,
                'following_id' => $users[$i]->id,
                'strength' => round(rand(10, 100) / 10, 2), // 1.0 - 10.0
            ]);
        }

        // Some users follow demo user back
        foreach (array_slice($users, 1, 5) as $user) {
            Relationship::create([
                'follower_id' => $user->id,
                'following_id' => $demoUser->id,
                'strength' => round(rand(10, 60) / 10, 2),
            ]);
        }

        // Cross-follows between other users
        for ($i = 1; $i < count($users) - 1; $i++) {
            if (rand(0, 1)) {
                Relationship::create([
                    'follower_id' => $users[$i]->id,
                    'following_id' => $users[$i + 1]->id,
                    'strength' => round(rand(10, 50) / 10, 2),
                ]);
            }
        }
    }

    private function createInteractions(array $users, $allPosts): void
    {
        $demoUser = $users[0];

        // Demo user reacts to ~40% of posts
        foreach ($allPosts->random((int) ($allPosts->count() * 0.4)) as $post) {
            if ($post->user_id === $demoUser->id) {
                continue; // Skip own posts
            }

            Interaction::create([
                'user_id' => $demoUser->id,
                'post_id' => $post->id,
                'type' => Interaction::TYPES[array_rand(Interaction::TYPES)],
            ]);
        }

        // Other users react to random posts
        foreach (array_slice($users, 1) as $user) {
            $sampleSize = min(rand(3, 10), $allPosts->count());
            foreach ($allPosts->random($sampleSize) as $post) {
                if ($post->user_id === $user->id) {
                    continue;
                }

                // Avoid duplicate constraint violations
                $exists = Interaction::where('user_id', $user->id)
                    ->where('post_id', $post->id)
                    ->where('type', Interaction::TYPES[0])
                    ->exists();

                if (! $exists) {
                    Interaction::create([
                        'user_id' => $user->id,
                        'post_id' => $post->id,
                        'type' => Interaction::TYPES[array_rand(Interaction::TYPES)],
                    ]);
                }
            }
        }
    }
}
