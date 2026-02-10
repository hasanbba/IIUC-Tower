<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_expense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('office_expense_groups')->cascadeOnDelete();
            $table->foreignId('head_id')->constrained('office_expense_heads')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_expense_items');
    }
};
