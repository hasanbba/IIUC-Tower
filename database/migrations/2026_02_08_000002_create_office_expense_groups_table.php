<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_expense_groups', function (Blueprint $table) {
            $table->id();
            $table->date('expense_date')->unique();
            $table->unsignedInteger('bill_no')->unique();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_expense_groups');
    }
};
