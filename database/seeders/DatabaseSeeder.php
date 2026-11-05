<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use App\Models\WebsiteInfo;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@mail.com',
            'password' => bcrypt('password'),
        ]);

        Admin::create([
            'name' => 'Viewer',
            'email' => 'viewer@mail.com',
            'password' => bcrypt('password'),
        ]);

        Admin::create([
            'name' => 'Shahin',
            'email' => 'mdshahinmije96@gmail.com',
            'password' => bcrypt('password'),
        ]);

        WebsiteInfo::create([]);
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            CategorySeeder::class,
        ]);
    }
}
