<?php

namespace App\Services;

use InvalidArgumentException;


class PricingCalculator
{
     /**
     * Calculate the maximum estimated cost.
     *
     * All monetary calculations use integer nanodollars.
     * 1 USD = 1,000,000,000 nanodollars.
     */
    public function estimate(
        string $prompt,
        string $size,
        string $quality,
        int $referenceImageCount,
        array $pricing
    ): array {
        $rates = $pricing['rates'];
        $tokens = $pricing['tokens'];

        if (! isset($tokens['output'][$quality][$size])) {
            throw new InvalidArgumentException(
                'Unsupported image size or quality.'
            );
        }

        if ($referenceImageCount < 0 || $referenceImageCount > 4) {
            throw new InvalidArgumentException(
                'Reference image count must be between 0 and 4.'
            );
        }

        // Estimate text tokens, including 15% variance.
        $characters = mb_strlen($prompt);
        $charactersPerToken = $tokens['characters_per_text_token'];

        $estimatedTextTokens = (int) ceil(
            $characters / $charactersPerToken
        );

        $inputTextTokens = (int) ceil(
            $estimatedTextTokens * (1 + $tokens['text_token_variance'])
        );

        // Each reference image consumes exactly 765 tokens.
        $inputImageTokens =
            $referenceImageCount * $tokens['per_reference_image'];

        // Output token count depends on quality and size.
        $outputTokens = $tokens['output'][$quality][$size];

        // Use maximum reasoning tokens for a conservative estimate.
        $reasoningTokens = $tokens['reasoning'][$quality]['max'];

        $breakdown = [
            'input_text_tokens' => $inputTextTokens,
            'input_image_tokens' => $inputImageTokens,
            'output_tokens' => $outputTokens,
            'reasoning_tokens' => $reasoningTokens,
        ];

        $providerCostNano = 0;

        foreach ($breakdown as $tokenType => $count) {
            $rateCents = $this->rateToCents($rates[$tokenType]);

            // Rate is USD per million tokens.
            // One cent per million tokens = 10 nanodollars per token.
            $providerCostNano += $count * $rateCents * 10;
        }

        // Customer price = provider cost + 25%.
        // Round upward to the next nanodollar.
        $customerCostNano = intdiv(
            ($providerCostNano * 125) + 99,
            100
        );

        return [
            'currency' => 'usd',
            'estimated_provider_cost' => $this->nanoToUsd($providerCostNano),
            'estimated_customer_price' => $this->nanoToUsd($customerCostNano),
            'markup_percentage' => 25,
            'token_breakdown' => $breakdown,
        ];
    }

    /**
     * Convert a two-decimal USD rate into integer cents.
     */
    private function rateToCents(string $rate): int
    {
        if (! preg_match('/^\d+\.\d{2}$/', $rate)) {
            throw new InvalidArgumentException(
                'Unexpected provider rate format.'
            );
        }

        [$whole, $decimal] = explode('.', $rate);

        return ((int) $whole * 100) + (int) $decimal;
    }

    /**
     * Convert integer nanodollars to an exact USD decimal string.
     */
    private function nanoToUsd(int $amount): string
    {
        return intdiv($amount, 1_000_000_000)
            . '.'
            . str_pad(
                (string) ($amount % 1_000_000_000),
                9,
                '0',
                STR_PAD_LEFT
            );
    }


    public function calculateActualCost(
    array $usage,
    array $rates
): array {
    $tokenTypes = [
        'input_text_tokens',
        'input_image_tokens',
        'output_tokens',
        'reasoning_tokens',
    ];

    $providerCostNano = 0;

    foreach ($tokenTypes as $tokenType) {
        $count = (int) ($usage[$tokenType] ?? 0);

        $rateCents = $this->rateToCents(
            $rates[$tokenType]
        );

        $providerCostNano += $count * $rateCents * 10;
    }

    $customerCostNano = intdiv(
        ($providerCostNano * 125) + 99,
        100
    );

    return [
        'actual_provider_cost' => $this->nanoToUsd(
            $providerCostNano
        ),
        'actual_customer_price' => $this->nanoToUsd(
            $customerCostNano
        ),
    ];
}
}
