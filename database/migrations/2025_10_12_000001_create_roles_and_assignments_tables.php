<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // roles table (global roles or tenant-scoped via nullable tenant_id)
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. admin, manager, member
            $table->string('slug')->unique(); // machine-readable key
            $table->boolean('is_system')->default(false); // protect system roles
            $table->timestamps();
        });

        // pivot for user-role assignments (optionally scoped to a tenant)
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'role_id', 'tenant_id']);
        });

        // optional: quick index for lookups
        Schema::table('role_user', function (Blueprint $table) {
            $table->index(['user_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
