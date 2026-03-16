<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LecturerPasswordFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_lecturer_with_temporary_password_is_redirected_to_profile(): void
    {
        $lecturerRole = Role::query()->create([
            'name' => 'lecturer',
            'guard_name' => 'web',
        ]);

        $lecturer = User::factory()->create([
            'password' => 'password',
            'must_change_password' => true,
            'is_active' => true,
        ]);

        $lecturer->assignRole($lecturerRole);

        $response = $this->post('/login', [
            'email' => $lecturer->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('profile.edit', absolute: false));
        $response->assertSessionHas('warning');
    }
}
