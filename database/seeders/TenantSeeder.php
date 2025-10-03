<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::create([
            'name' => 'Demo Tenant',
            'domain' => 'demo.ofroot.app',
            'plan' => 'pro',
            'settings' => [
                'timezone' => 'America/New_York',
                'theme' => 'light',
            ],
        ]);
    }
}
