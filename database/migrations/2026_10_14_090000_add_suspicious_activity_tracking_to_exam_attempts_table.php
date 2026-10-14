<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            // นับจำนวนครั้งที่ตรวจพบสัญญาณน่าสงสัย (สลับแท็บ/ออกจาก fullscreen) ระหว่างทำข้อสอบ
            $table->unsignedInteger('suspicious_activity_count')->default(0)->after('score');
            // เก็บรายละเอียดแต่ละครั้ง [{type, at}, ...] ให้ HR ดูประกอบตอนตรวจ
            $table->json('suspicious_activity_log')->nullable()->after('suspicious_activity_count');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumn(['suspicious_activity_count', 'suspicious_activity_log']);
        });
    }
};
