<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Define tenants to seed
        $tenants = [
            [
                'name' => 'Demo Tenant',
                'domain' => 'demo.ofroot.app',
                'plan' => 'pro',
                'settings' => [
                    'timezone' => 'America/New_York',
                    'theme' => 'light',
                ],
                'users' => [
                    [
                        'name' => 'Demo Admin',
                        'email' => 'admin@demo.ofroot.app',
                        'password' => bcrypt('password'),
                    ],
                ],
            ],
            [
                'name' => 'Test Tenant',
                'domain' => 'test.ofroot.app',
                'plan' => 'free',
                'settings' => [
                    'timezone' => 'Europe/London',
                    'theme' => 'dark',
                ],
                'users' => [
                    [
                        'name' => 'Test Admin',
                        'email' => 'admin@test.ofroot.app',
                        'password' => bcrypt('password'),
                    ],
                ],
            ],
        ];

        // Seed tenants and their users
        foreach ($tenants as $tenantData) {
            $tenant = Tenant::updateOrCreate(
                ['name' => $tenantData['name']],
                [
                    'domain' => $tenantData['domain'],
                    'plan' => $tenantData['plan'],
                    'settings' => $tenantData['settings'],
                ]
            );

            // Seed associated users for this tenant
            foreach ($tenantData['users'] as $userData) {
                $tenant->users()->updateOrCreate(
                    ['email' => $userData['email']],
                    $userData
                );
            }
        }

        $this->command->info('Tenants and their users seeded successfully!');
    }
}
