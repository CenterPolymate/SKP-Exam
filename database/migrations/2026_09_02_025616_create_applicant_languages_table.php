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
    Schema::create('applicant_languages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
        $table->enum('language', ['thai', 'english', 'chinese', 'other']);
        $table->string('custom_language_name')->nullable(); // ใช้เมื่อ language = other
        $table->unsignedTinyInteger('speaking_level');      // 0-5
        $table->unsignedTinyInteger('understanding_level');
        $table->unsignedTinyInteger('reading_level');
        $table->unsignedTinyInteger('writing_level');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_languages');
    }
};
