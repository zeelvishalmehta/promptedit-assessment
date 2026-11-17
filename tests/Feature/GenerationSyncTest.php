<?php

namespace Tests\Feature;

use App\Models\Generation;
use App\Models\User;
use App\Services\GenerationSyncService;
use App\Services\PixelforgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GenerationSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_generation_has_zero_actual_cost(): void
    {
        $user = User::factory()->create();

        $generation = $user->generations()->create([
            'request_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'provider_generation_id' => 'gen_test_failed',
            'prompt' => 'Test failed generation',
            'size' => '1024x1024',
            'quality' => 'medium',
            'status' => 'queued',
            'estimated_provider_cost' => '0.069060000',
            'estimated_customer_price' => '0.086325000',
        ]);

        $providerResponse = [
            'id' => 'gen_test_failed',
            'status' => 'failed',
            'error' => [
                'code' => 'generation_failed',
                'message' => 'The image could not be generated.',
            ],
            'completed_at' => now()->toISOString(),
        ];

        $pixelforge = Mockery::mock(PixelforgeService::class);

        $pixelforge->shouldReceive('getGeneration')
            ->once()
            ->with('gen_test_failed')
            ->andReturn($providerResponse);

        $this->app->instance(PixelforgeService::class, $pixelforge);

        app(GenerationSyncService::class)->sync($generation);

        $generation->refresh();

        $this->assertSame('failed', $generation->status);
        $this->assertSame('0.000000000', $generation->actual_provider_cost);
        $this->assertSame('0.000000000', $generation->actual_customer_price);
        $this->assertSame('generation_failed', $generation->error_code);
    }


public function test_completed_generation_cannot_revert_to_processing(): void
{
    $user = User::factory()->create();

    $generation = $user->generations()->create([
        'request_uuid' => (string) \Illuminate\Support\Str::uuid(),
        'provider_generation_id' => 'gen_test_completed',
        'prompt' => 'Test completed generation',
        'size' => '1024x1024',
        'quality' => 'medium',
        'status' => 'succeeded',
        'estimated_provider_cost' => '0.050000000',
        'estimated_customer_price' => '0.062500000',
        'actual_provider_cost' => '0.050000000',
        'actual_customer_price' => '0.062500000',
    ]);

    $pixelforge = Mockery::mock(PixelforgeService::class);

    $pixelforge->shouldReceive('getGeneration')
        ->once()
        ->with('gen_test_completed')
        ->andReturn([
            'id' => 'gen_test_completed',
            'status' => 'processing',
        ]);

    $this->app->instance(PixelforgeService::class, $pixelforge);

    app(GenerationSyncService::class)->sync($generation);

    $generation->refresh();

    $this->assertSame('succeeded', $generation->status);
    $this->assertSame('0.050000000', $generation->actual_provider_cost);
    $this->assertSame('0.062500000', $generation->actual_customer_price);
}

}
