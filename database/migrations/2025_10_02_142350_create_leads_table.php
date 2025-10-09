<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete(); // tenant optional
            $table->string('zip', 10)->index();          // user zip code
            $table->string('service', 100);             // type of service
            $table->string('name')->nullable();         // user name
            $table->string('phone', 50);                // user phone
            $table->string('email')->nullable();        // optional email
            $table->string('source')->nullable();       // landing page or campaign
            $table->string('status')->default('new');   // new, routed, accepted, failed
            $table->json('meta')->nullable();           // extensible JSON data
            $table->timestamps();                        // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
