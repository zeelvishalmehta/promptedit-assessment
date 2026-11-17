<?php

namespace App\Services;

use App\Models\Generation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GenerationSyncService
{
    public function __construct(
        private PixelforgeService $pixelforge,
        private PricingCalculator $calculator
    ) {}

    public function sync(Generation $generation): Generation
    {
        if (! $generation->provider_generation_id) {
            throw new RuntimeException(
                'Provider generation ID is missing.'
            );
        }

        // Get the latest status from Pixelforge.
        $result = $this->pixelforge->getGeneration(
            $generation->provider_generation_id
        );

        $status = $result['status'] ?? null;

        if (! in_array($status, [
            'queued',
            'processing',
            'succeeded',
            'failed',
        ], true)) {
            throw new RuntimeException(
                'Unknown provider generation status.'
            );
        }

        return DB::transaction(function () use (
            $generation,
            $result,
            $status
        ) {
            $record = Generation::whereKey($generation->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Never overwrite an already completed generation.
            if ($record->status === 'succeeded') {
                return $record;
                }

            if (
                $record->status === 'failed'
                && $status !== 'failed'
            ) {
                return $record;
            }

            // Do not move backwards from processing to queued.
            if (
                $record->status === 'processing'
                && $status === 'queued'
            ) {
                return $record;
            }

            $updates = [
                'status' => $status,
                'image_url' => $result['image_url'] ?? null,
                'image_url_expires_at' =>
                    isset($result['image_url_expires_at'])
                        ? Carbon::parse($result['image_url_expires_at'])
                        : null,
                'started_at' => isset($result['started_at'])
                    ? Carbon::parse($result['started_at'])
                    : null,
            ];

            if ($status === 'succeeded') {
                $usage = $result['usage'] ?? [];
                $rates = $result['rates'] ?? [];

                if (! $usage || ! $rates) {
                    throw new RuntimeException(
                        'Usage or pricing information is missing.'
                    );
                }

                $actualCosts = $this->calculator
                    ->calculateActualCost($usage, $rates);

                $updates['usage'] = $usage;
                $updates['rates'] = $rates;
                $updates['actual_provider_cost'] =
                    $actualCosts['actual_provider_cost'];
                $updates['actual_customer_price'] =
                    $actualCosts['actual_customer_price'];
                $updates['completed_at'] = isset($result['completed_at'])
                    ? Carbon::parse($result['completed_at'])
                    : now();
            }

           if ($status === 'failed') {
                $error = $result['error'] ?? [];

                $updates['actual_provider_cost'] = '0.000000000';
                $updates['actual_customer_price'] = '0.000000000';

                $updates['error_code'] = $error['code'] ?? null;
                $updates['error_message'] = $error['message'] ?? null;

                $updates['completed_at'] = isset($result['completed_at'])
                    ? Carbon::parse($result['completed_at'])
                    : now();
                }

            $record->update($updates);

            return $record->fresh();
        });
    }
}
