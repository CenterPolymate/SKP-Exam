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
    Schema::create('exam_answers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
        $table->foreignId('exam_question_id')->constrained()->cascadeOnDelete();
        $table->foreignId('selected_choice_id')->nullable()
            ->constrained('exam_question_choices')->nullOnDelete();
        $table->text('answer_text')->nullable();
        $table->string('answer_file_path')->nullable();
        $table->decimal('score_given', 6, 2)->nullable();
        $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('graded_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
    }
};
