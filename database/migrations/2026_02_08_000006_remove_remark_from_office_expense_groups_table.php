<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('office_expense_groups', function (Blueprint $table) {
            if (Schema::hasColumn('office_expense_groups', 'remark')) {
                $table->dropColumn('remark');
            }
        });
    }

    public function down(): void
    {
        Schema::table('office_expense_groups', function (Blueprint $table) {
            $table->text('remark')->nullable()->after('total_amount');
        });
    }
};
