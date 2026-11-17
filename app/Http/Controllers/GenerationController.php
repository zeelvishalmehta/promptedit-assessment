<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreGenerationRequest;
use App\Models\Generation;
use App\Services\PixelforgeService;
use App\Services\PricingCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GenerationController extends Controller
{
    public function store(
        StoreGenerationRequest $request,
        PixelforgeService $pixelforge,
        PricingCalculator $calculator
    ): JsonResponse {
        $validated = $request->validated();

        $referenceImages = $validated['reference_images'] ?? [];

        // 1. Get the latest provider pricing.
        $pricing = $pixelforge->getPricing();

        // 2. Calculate the estimated customer price.
        $estimate = $calculator->estimate(
            $validated['prompt'],
            $validated['size'],
            $validated['quality'],
            count($referenceImages),
            $pricing
        );

        // 3. Generate a unique request identifier.
        $requestUuid = (string) Str::uuid();

        // 4. Save our request before contacting Pixelforge.
        $generation = $request->user()
            ->generations()
            ->create([
                'request_uuid' => $requestUuid,
                'prompt' => $validated['prompt'],
                'size' => $validated['size'],
                'quality' => $validated['quality'],
                'reference_images' => $referenceImages,
                'status' => 'queued',
                'estimated_provider_cost' =>
                    $estimate['estimated_provider_cost'],
                'estimated_customer_price' =>
                    $estimate['estimated_customer_price'],
            ]);

        // 5. Prepare the exact provider request.
        $payload = [
            'prompt' => $validated['prompt'],
            'size' => $validated['size'],
            'quality' => $validated['quality'],
            'metadata' => [
                'request_uuid' => $requestUuid,
            ],
        ];

        if (! empty($referenceImages)) {
            $payload['reference_images'] = $referenceImages;
        }

        try {
            // 6. Submit the request to Pixelforge.
            $providerResponse = $pixelforge->createGeneration(
                $payload,
                $requestUuid
            );

            // 7. Save the provider's response.
            $generation->update([
                'provider_generation_id' => $providerResponse['id'],
                'status' => $providerResponse['status'],
                'image_url' => $providerResponse['image_url'] ?? null,
                'image_url_expires_at' =>
                    $providerResponse['image_url_expires_at'] ?? null,
                'rates' => $providerResponse['rates'] ?? $pricing['rates'],
            ]);

            return response()->json([
                'message' => 'Generation submitted successfully.',
                'generation' => $generation->fresh(),
            ], 202);

        } catch (RequestException $exception) {
            // Provider rejected the request.
            $error = $exception->response->json('error', []);

            $generation->update([
                'status' => 'failed',
                'error_code' => $error['code'] ?? 'provider_error',
                'error_message' =>
                    $error['message'] ?? 'Provider rejected the request.',
                'completed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Pixelforge could not accept the request.',
                'generation_id' => $generation->id,
                'error' => $generation->error_message,
            ], $exception->response->status());

        } catch (ConnectionException $exception) {
            // The request may have reached Pixelforge.
            // Keep the record queued so we can reconcile it safely.
            report($exception);

            return response()->json([
                'message' => 'The provider response could not be confirmed. '
                    . 'The request has been saved for reconciliation.',
                'generation_id' => $generation->id,
                'request_uuid' => $requestUuid,
            ], 503);
        }
    }


    public function create()
{
    return Inertia::render('Generations/Create');
}

public function estimate(
    StoreGenerationRequest $request,
    PixelforgeService $pixelforge,
    PricingCalculator $calculator
): JsonResponse {
    $validated = $request->validated();

    $pricing = $pixelforge->getPricing();

    $estimate = $calculator->estimate(
        $validated['prompt'],
        $validated['size'],
        $validated['quality'],
        count($validated['reference_images'] ?? []),
        $pricing
    );

    return response()->json($estimate);
}

public function index()
{
    $generations = request()->user()
        ->generations()
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return Inertia::render('Generations/Index', [
        'generations' => $generations,
    ]);
}
}
