<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Database\Seeders\AdminRootSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RootAdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_admin_is_seeded_with_admin_guard_role(): void
    {
        config()->set('app.env', 'testing');

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(AdminRootSeeder::class);

        $root = Admin::query()->where('email', env('EMS_ROOT_ADMIN_EMAIL', 'root@ems.local'))->first();

        $this->assertNotNull($root);
        $this->assertTrue($root->is_super_admin);
        $this->assertTrue($root->is_active);
        $this->assertTrue($root->must_change_password);
        $this->assertTrue($root->hasRole('root_admin'));
    }
}
