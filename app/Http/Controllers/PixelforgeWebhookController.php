<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessPixelforgeWebhook;
use App\Models\WebhookEvent;

class PixelforgeWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        // 1. Get the original, unmodified request body.
        $payload = $request->getContent();

        // 2. Read the signature header.
        $signatureHeader = (string) $request->header(
            'Pixelforge-Signature'
        );

        $signature = [];

        foreach (explode(',', $signatureHeader) as $part) {
            [$key, $value] = array_pad(
                explode('=', $part, 2),
                2,
                ''
            );

            $signature[$key] = $value;
        }

        $timestamp = (int) ($signature['t'] ?? 0);
        $receivedSignature = $signature['v1'] ?? '';

        // 3. Calculate the expected HMAC signature.
        $secret = config('services.pixelforge.webhook_secret');

        if (! $secret || ! $timestamp || ! $receivedSignature) {
            abort(400, 'Missing webhook signature.');
        }

        $expectedSignature = hash_hmac(
            'sha256',
            "{$timestamp}.{$payload}",
            $secret
        );

        // 4. Verify signature and reject old requests.
        if (
            ! hash_equals($expectedSignature, $receivedSignature)
            || abs(time() - $timestamp) > 300
        ) {
            abort(400, 'Invalid Pixelforge signature.');
        }

        // 5. Decode the event.
        $event = json_decode($payload, true);

        if (
            ! is_array($event)
            || ! isset($event['id'], $event['type'])
            || ! str_starts_with($event['id'], 'evt_')
        ) {
            abort(400, 'Invalid webhook event.');
        }

        // 6. Confirm the event headers match its body.
        if (
            $request->header('Pixelforge-Event-Id') !== $event['id']
            || $request->header('Pixelforge-Event-Type') !== $event['type']
        ) {
            abort(400, 'Webhook event headers do not match.');
        }

        // 7. Extract the provider generation ID.
        $providerGenerationId =
            $event['data']['object']['id'] ?? null;

        // 8. Store the event only once.
        $inserted = DB::table('webhook_events')->insertOrIgnore([
            'provider_event_id' => $event['id'],
            'event_type' => $event['type'],
            'provider_generation_id' => $providerGenerationId,
            'payload' => json_encode($event),
            'processed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $webhookEvent = WebhookEvent::where(
                        'provider_event_id',
                         $event['id']
                        )->firstOrFail();

        // Dispatch pending events, including retries of earlier deliveries.
        if (! $webhookEvent->processed_at) {
            ProcessPixelforgeWebhook::dispatch($webhookEvent->id);
        }


        if (! $inserted) {
            Log::info('Duplicate Pixelforge webhook received.', [
                'event_id' => $event['id'],
            ]);
        }

        // Acknowledge receipt.
        return response()->noContent();
    }
}
