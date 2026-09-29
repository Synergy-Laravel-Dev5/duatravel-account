<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {

            $this->call(PermissionTableSeeder::class);

            $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

            $adminRole->syncPermissions(Permission::all());

            $adminUser = User::firstOrCreate(
                ['email' => 'admin@gmail.com'],
                [
                    'name' => 'Admin User',
                    'password' => Hash::make('Admin@12345'),
                    'status' => 'active',
                ]
            );

            $adminUser->assignRole($adminRole);

            DB::commit();

            $this->command->info('✅ Admin user, role, and all permissions created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Seeder failed: ' . $e->getMessage());
        }
    }
}
