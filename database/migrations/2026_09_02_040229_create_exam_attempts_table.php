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
    Schema::create('exam_attempts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('exam_assignment_id')->constrained()->cascadeOnDelete();
        $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
        $table->timestamp('started_at')->nullable();
        $table->timestamp('submitted_at')->nullable();
        $table->enum('status', [
            'not_started', 'in_progress', 'awaiting_review', 'passed', 'failed',
        ])->default('not_started');
        $table->decimal('score', 6, 2)->nullable();
        $table->timestamps();

        $table->unique(['exam_assignment_id', 'exam_id']); // 1 ชุดข้อสอบ ทำได้ 1 ครั้งต่อ assignment
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
