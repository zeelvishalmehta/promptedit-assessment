<?php

namespace App\Console\Commands;

use App\Models\Generation;
use App\Services\GenerationSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncPendingGenerations extends Command
{
    protected $signature = 'generations:sync-pending';

    protected $description = 'Synchronize pending generations with Pixelforge';

    public function handle(GenerationSyncService $syncService): int
    {
        $generations = Generation::query()
            ->whereIn('status', ['queued', 'processing'])
            ->whereNotNull('provider_generation_id')
            ->get();

        $this->info(
            "Found {$generations->count()} pending generations."
        );

        foreach ($generations as $generation) {
            try {
                $syncService->sync($generation);

                $this->info(
                    "Generation #{$generation->id} synchronized."
                );
            } catch (Throwable $exception) {
                Log::error('Generation polling failed.', [
                    'generation_id' => $generation->id,
                    'error' => $exception->getMessage(),
                ]);

                $this->error(
                    "Generation #{$generation->id} could not be synchronized."
                );
            }
        }

        return self::SUCCESS;
    }
}
