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
        DB::statement("UPDATE exam_questions SET question_type = 'single_choice' WHERE question_type = 'multiple_choice'");

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE exam_questions MODIFY COLUMN question_type ENUM('single_choice', 'multi_choice', 'essay') NOT NULL");

            return;
        }

        DB::statement('ALTER TABLE exam_questions DROP CONSTRAINT exam_questions_question_type_check');
        DB::statement("ALTER TABLE exam_questions ADD CONSTRAINT exam_questions_question_type_check CHECK ((question_type)::text = ANY ((ARRAY['single_choice'::character varying, 'multi_choice'::character varying, 'essay'::character varying])::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE exam_questions SET question_type = 'multiple_choice' WHERE question_type IN ('single_choice', 'multi_choice')");

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE exam_questions MODIFY COLUMN question_type ENUM('multiple_choice', 'essay') NOT NULL");

            return;
        }

        DB::statement('ALTER TABLE exam_questions DROP CONSTRAINT exam_questions_question_type_check');
        DB::statement("ALTER TABLE exam_questions ADD CONSTRAINT exam_questions_question_type_check CHECK ((question_type)::text = ANY ((ARRAY['multiple_choice'::character varying, 'essay'::character varying])::text[]))");
    }
};
