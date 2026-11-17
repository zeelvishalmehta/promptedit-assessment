<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Generation extends Model
{
    protected $fillable = [
        'user_id',
        'request_uuid',
        'provider_generation_id',
        'prompt',
        'size',
        'quality',
        'reference_images',
        'status',
        'image_url',
        'image_url_expires_at',
        'estimated_provider_cost',
        'estimated_customer_price',
        'actual_provider_cost',
        'actual_customer_price',
        'usage',
        'rates',
        'error_code',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'reference_images' => 'array',
            'usage' => 'array',
            'rates' => 'array',
            'estimated_provider_cost' => 'decimal:9',
            'estimated_customer_price' => 'decimal:9',
            'actual_provider_cost' => 'decimal:9',
            'actual_customer_price' => 'decimal:9',
            'image_url_expires_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
