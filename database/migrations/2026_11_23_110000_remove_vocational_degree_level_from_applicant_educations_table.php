<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * เลิกใช้ระดับการศึกษา "วิชาชีพ" (vocational)
     * ใบสมัครเดิมที่เคยเลือกระดับนี้จะถูกย้ายไปเป็น "อื่นๆ" (other) โดยเก็บชื่อสถาบัน/สาขา/เกรดไว้ครบ ไม่ลบแถวข้อมูล
     */
    public function up(): void
    {
        DB::table('applicant_educations')->where('degree_level', 'vocational')->update(['degree_level' => 'other']);

        Schema::table('applicant_educations', function (Blueprint $table) {
            $table->enum('degree_level', [
                'junior_high', 'senior_high_vocational_cert', 'diploma_high_vocational',
                'bachelor', 'master', 'other',
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('applicant_educations', function (Blueprint $table) {
            $table->enum('degree_level', [
                'junior_high', 'senior_high_vocational_cert', 'diploma_high_vocational',
                'vocational', 'bachelor', 'master', 'other',
            ])->change();
        });
    }
};
