<?php

/**
 * Ranking algorithm configuration.
 *
 * All weights and parameters are adjustable without code changes.
 * Weights should sum to 1.0 for normalized scoring.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Signal Weights
    |--------------------------------------------------------------------------
    |
    | These weights control how much each signal contributes to the final
    | ranking score. They should sum to 1.0.
    |
    */
    'weights' => [
        'authenticity' => (float) env('RANKING_WEIGHT_AUTHENTICITY', 0.30),
        'relationship' => (float) env('RANKING_WEIGHT_RELATIONSHIP', 0.25),
        'similarity'   => (float) env('RANKING_WEIGHT_SIMILARITY', 0.25),
        'recency'      => (float) env('RANKING_WEIGHT_RECENCY', 0.20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Time Decay
    |--------------------------------------------------------------------------
    |
    | Controls how quickly posts lose recency relevance.
    | Higher values = faster decay. Default 0.05 means:
    |   6h → 0.74, 24h → 0.30, 48h → 0.09
    |
    */
    'decay_rate' => (float) env('RANKING_DECAY_RATE', 0.05),

    /*
    |--------------------------------------------------------------------------
    | Candidate Pool
    |--------------------------------------------------------------------------
    |
    | Number of recent posts to consider before applying ranking.
    | Keeps computation bounded for large datasets.
    |
    */
    'candidate_pool_size' => (int) env('RANKING_POOL_SIZE', 200),

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'per_page_default' => 20,
    'per_page_max' => 50,

    /*
    |--------------------------------------------------------------------------
    | Similarity Defaults
    |--------------------------------------------------------------------------
    |
    | Default similarity score when user has no interaction history.
    |
    */
    'default_similarity' => 0.5,

];
