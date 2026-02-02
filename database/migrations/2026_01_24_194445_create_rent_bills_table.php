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
        Schema::create('rent_bills', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('bill_month');
            $table->string('client_name');
            $table->json('rent_items')->nullable();
            $table->decimal('rent', 12, 2)->default(0);
            $table->integer('parking_qty')->default(0);
            $table->decimal('parking_rate', 12, 2)->default(0);
            $table->decimal('parking_total', 12, 2)->default(0);
            $table->decimal('others_cost', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('income_tax', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('rent_advance', 12, 2)->default(0);
            $table->decimal('amount_to_pay', 12, 2)->default(0);
            $table->decimal('vat_percent', 5, 2)->default(0);
            $table->decimal('vat_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->enum('status', ['ready','notready'])->default('notready');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_bills');
    }
};
