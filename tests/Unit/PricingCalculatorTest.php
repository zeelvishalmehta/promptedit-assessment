<?php

namespace Tests\Unit;

use App\Services\PricingCalculator;
use PHPUnit\Framework\TestCase;

class PricingCalculatorTest extends TestCase
{
    public function test_it_calculates_actual_provider_cost_and_customer_price(): void
    {
        $calculator = new PricingCalculator();

        $usage = [
            'input_text_tokens' => 40,
            'input_image_tokens' => 0,
            'output_tokens' => 1100,
            'reasoning_tokens' => 1561,
        ];

        $rates = [
            'input_text_tokens' => '5.00',
            'input_image_tokens' => '10.00',
            'output_tokens' => '40.00',
            'reasoning_tokens' => '12.50',
        ];

        $result = $calculator->calculateActualCost($usage, $rates);

        $this->assertSame('0.063712500', $result['actual_provider_cost']);
        $this->assertSame('0.079640625', $result['actual_customer_price']);
    }
}
