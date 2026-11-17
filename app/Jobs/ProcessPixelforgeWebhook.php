<?php

namespace App\Jobs;

use App\Models\Generation;
use App\Models\WebhookEvent;
use App\Services\GenerationSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class ProcessPixelforgeWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 6;

    public function __construct(
        public int $webhookEventId
    ) {}

    public function backoff(): array
    {
        return [10, 30, 120, 600, 900];
    }

    public function handle(
        GenerationSyncService $syncService
    ): void {
        $webhookEvent = WebhookEvent::findOrFail(
            $this->webhookEventId
        );

        if ($webhookEvent->processed_at) {
            return;
        }

        $event = $webhookEvent->payload;

        // Test events do not represent image generations.
        if (($event['type'] ?? '') === 'ping') {
            $webhookEvent->update([
                'processed_at' => now(),
            ]);

            return;
        }

        $providerGeneration =
            $event['data']['object'] ?? null;

        if (! is_array($providerGeneration)) {
            throw new RuntimeException(
                'Webhook generation data is missing.'
            );
        }

        $providerId = $providerGeneration['id'] ?? null;
        $requestUuid =
            $providerGeneration['metadata']['request_uuid'] ?? null;

        // First try the provider ID.
        $generation = $providerId
            ? Generation::where(
                'provider_generation_id',
                $providerId
            )->first()
            : null;

        // If the webhook arrived early, match by our UUID.
        if (! $generation && $requestUuid) {
            $generation = Generation::where(
                'request_uuid',
                $requestUuid
            )->first();
        }

        if (! $generation) {
            // Laravel will retry this job.
            throw new RuntimeException(
                'Local generation record not found yet.'
            );
        }

        if (! $generation->provider_generation_id) {
            $generation->update([
                'provider_generation_id' => $providerId,
            ]);
        }

        // Fetch the latest provider status and update our database.
        $syncService->sync($generation);

        $webhookEvent->update([
            'processed_at' => now(),
        ]);
    }
}
