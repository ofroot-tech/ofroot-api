<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use App\Models\User;

/**
 * AdminPasswordResetSeeder — a tiny, env-gated, one-off password reset.
 *
 * Intent
 *  On platforms without shell access, we sometimes need a safe way to
 *  reset a specific admin's password exactly once via environment variables
 *  on deploy. This seeder implements that in a production-safe manner:
 *
 *  - Runs only when explicitly invoked (never automatically).
 *  - Requires RESET_ADMIN_PASSWORD_ON_BOOT=true at the container level to be
 *    executed via Docker CMD or an explicit db:seed call.
 *  - Requires ADMIN_RESET_EMAIL and ADMIN_RESET_PASSWORD to be set.
 *  - Never logs passwords; only emits non-sensitive status.
 *
 * Usage (example via Docker CMD)
 *  php artisan db:seed --class='Database\\Seeders\\AdminPasswordResetSeeder' --force
 */
class AdminPasswordResetSeeder extends Seeder
{
    public function run(): void
    {
        // Environment guard: we operate in any env, but only when vars are set.
        $email = (string) env('ADMIN_RESET_EMAIL', '');
        $password = (string) env('ADMIN_RESET_PASSWORD', '');

        if ($email === '' || $password === '') {
            $this->command?->warn('AdminPasswordResetSeeder: Missing ADMIN_RESET_EMAIL or ADMIN_RESET_PASSWORD. Skipping.');
            return;
        }

        // Find the user by email.
        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->command?->warn("AdminPasswordResetSeeder: No user found for email '{$email}'. Skipping.");
            return;
        }

        // Update the password securely.
        $user->password = Hash::make($password);
        $user->save();

        $this->command?->info("AdminPasswordResetSeeder: Password updated for '{$email}'.");
        $this->command?->info('Reminder: Unset ADMIN_RESET_PASSWORD and RESET_ADMIN_PASSWORD_ON_BOOT after confirmation.');
    }
}
