<?php

namespace Tests\Feature;

use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PixelforgeWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_webhook_is_stored_only_once(): void
    {
        Queue::fake();

        $payload = [
            'id' => 'evt_test_duplicate_001',
            'object' => 'event',
            'type' => 'ping',
            'created_at' => now()->toISOString(),
            'data' => [
                'object' => [],
            ],
        ];

        $this->postSignedWebhook($payload)->assertNoContent();

        $this->postSignedWebhook($payload)->assertNoContent();

        $this->assertSame(
            1,
            WebhookEvent::where(
                'provider_event_id',
                'evt_test_duplicate_001'
            )->count()
        );
    }

    private function postSignedWebhook(array $payload)
    {
        $body = json_encode($payload);
        $timestamp = now()->timestamp;

        $signature = hash_hmac(
            'sha256',
            $timestamp . '.' . $body,
            config('services.pixelforge.webhook_secret')
        );

        return $this->call(
            'POST',
            '/webhooks/pixelforge',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_PIXELFORGE_EVENT_ID' => $payload['id'],
                'HTTP_PIXELFORGE_EVENT_TYPE' => $payload['type'],
                'HTTP_PIXELFORGE_SIGNATURE' =>
                    "t={$timestamp},v1={$signature}",
            ],
            $body
        );
    }
}
