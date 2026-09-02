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
    Schema::create('applicant_educations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
        $table->enum('degree_level', [
            'junior_high', 'senior_high_vocational_cert', 'diploma_high_vocational',
            'vocational', 'bachelor', 'master', 'other',
        ]);
        $table->string('institution_name');
        $table->string('province_country')->nullable();
        $table->unsignedSmallInteger('start_year')->nullable(); // พ.ศ.
        $table->unsignedSmallInteger('end_year')->nullable();
        $table->string('major')->nullable();
        $table->decimal('gpa', 3, 2)->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_education');
    }
};
