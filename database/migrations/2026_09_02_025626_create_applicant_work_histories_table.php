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
    Schema::create('applicant_work_histories', function (Blueprint $table) {
        $table->id();
        $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
        $table->unsignedTinyInteger('order_no'); // 1-4
        $table->string('company_name');
        $table->string('position');
        $table->unsignedSmallInteger('start_year')->nullable(); // พ.ศ.
        $table->unsignedSmallInteger('end_year')->nullable();
        $table->string('supervisor_name')->nullable();
        $table->string('supervisor_phone')->nullable();
        $table->decimal('last_salary', 10, 2)->nullable();
        $table->text('resignation_reason')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_work_histories');
    }
};
