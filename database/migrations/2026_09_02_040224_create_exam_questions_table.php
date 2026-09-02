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
    Schema::create('exam_questions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
        $table->enum('question_type', ['multiple_choice', 'essay']);
        $table->enum('content_format', ['text', 'image']); // รูปแบบโจทย์
        $table->text('question_text')->nullable();
        $table->string('question_image_path')->nullable();
        $table->decimal('points', 6, 2);
        $table->enum('answer_format', ['text', 'file'])->nullable(); // ใช้เฉพาะ essay
        $table->unsignedSmallInteger('order_no')->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
