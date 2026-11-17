
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // UUID used for idempotency and webhook correlation.
            $table->uuid('request_uuid')->unique();

            // ID returned by Pixelforge.
            $table->string('provider_generation_id')
                ->nullable()
                ->unique();

            $table->text('prompt');
            $table->string('size', 20);
            $table->string('quality', 20);

            $table->json('reference_images')->nullable();

            $table->string('status', 30)->default('queued')->index();

            $table->text('image_url')->nullable();
            $table->timestamp('image_url_expires_at')->nullable();

            // Estimated costs before generation.
            $table->decimal('estimated_provider_cost', 18, 9);
            $table->decimal('estimated_customer_price', 18, 9);

            // Actual costs after successful generation.
            $table->decimal('actual_provider_cost', 18, 9)->nullable();
            $table->decimal('actual_customer_price', 18, 9)->nullable();

            // Preserve exact provider usage and rates.
            $table->json('usage')->nullable();
            $table->json('rates')->nullable();

            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generations');
    }
};
