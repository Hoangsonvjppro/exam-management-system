<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMustChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_with_must_change_password_is_redirected_to_own_edit_page(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = Admin::query()->create([
            'name' => 'Root Admin',
            'email' => 'must-change@ems.local',
            'password' => 'password',
            'must_change_password' => true,
            'is_active' => true,
            'is_super_admin' => true,
        ]);

        $admin->assignRole('root_admin');

        $this->actingAs($admin, 'admin')
            ->get('/admin')
            ->assertRedirect(route('filament.admin.resources.admins.edit', ['record' => $admin->id], absolute: false));
    }
}
