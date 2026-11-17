<?php

namespace App\Services;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class PixelforgeService
{
    /**
     * Create an authenticated API client.
     */
    private function client(): PendingRequest
    {
        return Http::baseUrl(
            rtrim(config('services.pixelforge.base_url'), '/')
        )
            ->acceptJson()
            ->withToken(config('services.pixelforge.api_key'))
            ->timeout(20);
    }

    /**
     * Retrieve current provider pricing.
     */
    public function getPricing(): array
    {
        return Http::baseUrl(
            rtrim(config('services.pixelforge.base_url'), '/')
        )
            ->acceptJson()
            ->timeout(10)
            ->get('/pricing')
            ->throw()
            ->json();
    }

    /**
     * Create an image generation.
     */
    public function createGeneration(
        array $payload,
        string $idempotencyKey
    ): array {
        return $this->client()
            ->withHeaders([
                'Idempotency-Key' => $idempotencyKey,
            ])
            ->post('/generations', $payload)
            ->throw()
            ->json();
    }

    /**
     * Retrieve a single generation.
     */
    public function getGeneration(string $generationId): array
    {
        return $this->client()
            ->get('/generations/' . rawurlencode($generationId))
            ->throw()
            ->json();
    }

    /**
     * List generations with optional filters.
     */
    public function listGenerations(array $query = []): array
    {
        return $this->client()
            ->get('/generations', $query)
            ->throw()
            ->json();
    }
}
