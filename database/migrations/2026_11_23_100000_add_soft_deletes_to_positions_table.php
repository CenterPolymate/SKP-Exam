<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ลบตำแหน่งแบบ soft delete: ตำแหน่งหายจากหน้าสมัคร/หน้าจัดการ แต่แถวยังอยู่ในฐานข้อมูล
     * ใบสมัครเดิมจึงยังอ้างอิงชื่อตำแหน่งได้ (เพิ่มคอลัมน์ nullable อย่างเดียว ไม่แตะข้อมูลเดิม)
     */
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
