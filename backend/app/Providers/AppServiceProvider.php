<?php

namespace App\Providers;

use App\Contracts\EmbeddingProvider;
use App\Contracts\RankingStrategy;
use App\Services\EmbeddingClient;
use App\Services\RankingService;
use Illuminate\Support\ServiceProvider;

/**
 * Binds contracts to concrete implementations.
 *
 * This is the single place to swap providers:
 * - EmbeddingProvider → EmbeddingClient (HTTP to Python service)
 * - RankingStrategy → RankingService (weighted scoring)
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EmbeddingProvider::class, EmbeddingClient::class);
        $this->app->bind(RankingStrategy::class, RankingService::class);
    }

    public function boot(): void
    {
        //
    }
}
