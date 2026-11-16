<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * หลักฐานการยินยอมนโยบายคุ้มครองข้อมูลส่วนบุคคล (PDPA) ที่หน้าแรกก่อนกรอกใบสมัคร
     * เก็บเวลาและเวอร์ชันของนโยบายที่ผู้สมัครยินยอม
     */
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->timestamp('pdpa_consented_at')->nullable();
            $table->string('pdpa_policy_version', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn(['pdpa_consented_at', 'pdpa_policy_version']);
        });
    }
};
