<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE applicants MODIFY COLUMN status ENUM('draft', 'submitted', 'qualified', 'disqualified', 'code_generated', 'awaiting_review', 'passed_test', 'failed_test') NOT NULL DEFAULT 'draft'");

            return;
        }

        DB::statement('ALTER TABLE applicants DROP CONSTRAINT applicants_status_check');
        DB::statement("ALTER TABLE applicants ADD CONSTRAINT applicants_status_check CHECK ((status)::text = ANY ((ARRAY['draft'::character varying, 'submitted'::character varying, 'qualified'::character varying, 'disqualified'::character varying, 'code_generated'::character varying, 'awaiting_review'::character varying, 'passed_test'::character varying, 'failed_test'::character varying])::text[]))");
        DB::statement("ALTER TABLE applicants ALTER COLUMN status SET DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE applicants SET status = 'submitted' WHERE status = 'draft'");

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE applicants MODIFY COLUMN status ENUM('submitted', 'qualified', 'disqualified', 'code_generated', 'awaiting_review', 'passed_test', 'failed_test') NOT NULL DEFAULT 'submitted'");

            return;
        }

        DB::statement('ALTER TABLE applicants DROP CONSTRAINT applicants_status_check');
        DB::statement("ALTER TABLE applicants ADD CONSTRAINT applicants_status_check CHECK ((status)::text = ANY ((ARRAY['submitted'::character varying, 'qualified'::character varying, 'disqualified'::character varying, 'code_generated'::character varying, 'awaiting_review'::character varying, 'passed_test'::character varying, 'failed_test'::character varying])::text[]))");
        DB::statement("ALTER TABLE applicants ALTER COLUMN status SET DEFAULT 'submitted'");
    }
};
