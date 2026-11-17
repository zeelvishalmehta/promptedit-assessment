<?php

namespace Tests\Feature;

use App\Models\Generation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_only_see_their_own_generations(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $generationA = $userA->generations()->create([
            'request_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'prompt' => 'User A private image',
            'size' => '1024x1024',
            'quality' => 'medium',
            'status' => 'succeeded',
            'estimated_provider_cost' => '0.050000000',
            'estimated_customer_price' => '0.062500000',
        ]);

        $userB->generations()->create([
            'request_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'prompt' => 'User B private image',
            'size' => '1024x1024',
            'quality' => 'medium',
            'status' => 'succeeded',
            'estimated_provider_cost' => '0.050000000',
            'estimated_customer_price' => '0.062500000',
        ]);

        $response = $this->actingAs($userA)
            ->get(route('generations.index'));

        $response->assertOk();

        $response->assertInertia(fn ($page) => $page
            ->component('Generations/Index')
            ->has('generations.data', 1)
            ->where('generations.data.0.id', $generationA->id)
            ->where('generations.data.0.prompt', 'User A private image')
        );
    }

    public function test_guest_cannot_access_generation_history(): void
    {
        $response = $this->get(route('generations.index'));

        $response->assertRedirect(route('login'));
    }
}
