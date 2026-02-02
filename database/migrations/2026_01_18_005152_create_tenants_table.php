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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('client_id')->unique();
            $table->decimal('rent_increase', 5, 2)->default(0);
            $table->text('contact_address')->nullable();
            $table->integer('agreement_year');
            $table->date('rent_start_date');
            $table->date('expired_date')->nullable();
            $table->string('floor')->nullable();
            $table->json('rent_items')->nullable();
            $table->decimal('total_rent', 12, 2)->default(0);
            $table->decimal('rent_advance', 12, 2)->default(0);
            $table->enum('status', ['active', 'expired', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
