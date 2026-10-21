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
        DB::statement("UPDATE exam_questions SET question_type = 'single_choice' WHERE question_type = 'multiple_choice'");

        Schema::table('exam_questions', function (Blueprint $table) {
            $table->enum('question_type', ['single_choice', 'multi_choice', 'essay'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE exam_questions SET question_type = 'multiple_choice' WHERE question_type IN ('single_choice', 'multi_choice')");

        Schema::table('exam_questions', function (Blueprint $table) {
            $table->enum('question_type', ['multiple_choice', 'essay'])->change();
        });
    }
};
