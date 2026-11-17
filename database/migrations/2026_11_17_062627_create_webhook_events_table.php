
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();

            // Pixelforge event ID: prevents duplicate processing.
            $table->string('provider_event_id')->unique();

            $table->string('event_type', 100);

            $table->string('provider_generation_id')
                ->nullable()
                ->index();

            // Keep the original event for auditing and debugging.
            $table->json('payload');

            $table->timestamp('processed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};
