<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\WebsiteInfo;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
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
            CatalogPermissionSeeder::class,
            BillingPermissionSeeder::class,
            LabPermissionSeeder::class,
            AccountsPermissionSeeder::class,
            ReportPermissionSeeder::class,
            WebsiteContentSeeder::class,
        ]);

        if (app()->isLocal()) {
            $this->call([DemoCatalogSeeder::class, DemoReferrerSeeder::class, DemoWebsiteSeeder::class]);
        }
    }
}
