<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InactiveUserAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_is_logged_out_by_active_middleware(): void
    {
        $inactiveUser = User::factory()->create([
            'is_active' => false,
        ]);

        $response = $this->actingAs($inactiveUser)->get('/dashboard');

        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }
}
