<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->enum('status', [
                'draft', 'submitted', 'qualified', 'disqualified', 'code_generated',
                'awaiting_review', 'passed_test', 'failed_test',
            ])->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE applicants SET status = 'submitted' WHERE status = 'draft'");

        Schema::table('applicants', function (Blueprint $table) {
            $table->enum('status', [
                'submitted', 'qualified', 'disqualified', 'code_generated',
                'awaiting_review', 'passed_test', 'failed_test',
            ])->default('submitted')->change();
        });
    }
};
