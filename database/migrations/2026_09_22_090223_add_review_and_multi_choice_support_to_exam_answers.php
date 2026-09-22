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
        Schema::table('exam_answers', function (Blueprint $table) {
            $table->jsonb('selected_choice_ids')->nullable()->after('selected_choice_id');
            $table->boolean('marked_for_review')->default(false)->after('answer_file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_answers', function (Blueprint $table) {
            $table->dropColumn(['selected_choice_ids', 'marked_for_review']);
        });
    }
};
