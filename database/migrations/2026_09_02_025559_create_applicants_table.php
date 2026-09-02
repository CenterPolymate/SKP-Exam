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
    Schema::create('applicants', function (Blueprint $table) {
        $table->id();
        $table->foreignId('position_id')->constrained()->cascadeOnDelete();
        $table->decimal('expected_salary', 10, 2)->nullable();

        // ชื่อ-นามสกุล
        $table->string('title_th');
        $table->string('first_name_th');
        $table->string('last_name_th');
        $table->string('nickname_th')->nullable();
        $table->string('title_en')->nullable();
        $table->string('first_name_en')->nullable();
        $table->string('last_name_en')->nullable();
        $table->string('nickname_en')->nullable();

        // ข้อมูลส่วนตัว
        $table->date('date_of_birth');
        $table->string('nationality');
        $table->string('ethnicity')->nullable();
        $table->string('religion')->nullable();
        $table->enum('gender', ['male', 'female', 'other']);
        $table->decimal('height_cm', 5, 1)->nullable();
        $table->decimal('weight_kg', 5, 1)->nullable();
        $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed', 'separated'])->nullable();
        $table->boolean('marriage_registered')->nullable();

        // สุขภาพ/ประวัติ
        $table->boolean('has_congenital_disease')->default(false);
        $table->string('congenital_disease_detail')->nullable();
        $table->boolean('has_criminal_record')->default(false);
        $table->string('criminal_record_detail')->nullable();
        $table->boolean('has_lese_majeste_record')->default(false);
        $table->string('lese_majeste_detail')->nullable();

        // ที่อยู่/ติดต่อ
        $table->string('hometown')->nullable();
        $table->string('country')->nullable();
        $table->text('current_address');
        $table->string('phone');
        $table->string('email');
        $table->string('line_id')->nullable();
        $table->string('facebook')->nullable();
        $table->string('instagram')->nullable();
        $table->string('twitter')->nullable();

        // บัตรประชาชน
        $table->string('national_id_number');
        $table->string('national_id_back_number')->nullable();
        $table->date('id_issue_date')->nullable();
        $table->date('id_expiry_date')->nullable();

        // ครอบครัว
        $table->enum('living_status', ['own_home', 'rent_home', 'with_parents', 'with_other'])->nullable();
        $table->string('spouse_name')->nullable();
        $table->string('spouse_occupation')->nullable();
        $table->unsignedTinyInteger('children_count')->nullable();

        // บิดามารดา (inline ไม่แยกตาราง)
        $table->string('father_name')->nullable();
        $table->unsignedTinyInteger('father_age')->nullable();
        $table->string('father_occupation')->nullable();
        $table->boolean('father_alive')->nullable();
        $table->string('mother_name')->nullable();
        $table->unsignedTinyInteger('mother_age')->nullable();
        $table->string('mother_occupation')->nullable();
        $table->boolean('mother_alive')->nullable();
        $table->unsignedTinyInteger('siblings_count')->nullable();
        $table->unsignedTinyInteger('birth_order')->nullable();

        $table->enum('military_status', ['exempted', 'completed', 'awaiting_conscription'])->nullable();

        // ภาษา - ใบรับรอง (ข้อความรวม ไม่ผูกกับภาษาใดภาษาหนึ่ง)
        $table->text('language_certificate_note')->nullable();

        // ข้อมูลอื่นๆ ก่อนส่ง
        $table->text('special_skills')->nullable();
        $table->boolean('guarantor_required_ok')->nullable();
        $table->boolean('shift_work_ok')->nullable();
        $table->decimal('informal_debt_amount', 10, 2)->nullable();
        $table->decimal('informal_debt_interest_rate', 5, 2)->nullable();
        $table->decimal('credit_card_debt_amount', 10, 2)->nullable();
        $table->decimal('total_outstanding_balance', 10, 2)->nullable();
        $table->text('illness_or_accident_history')->nullable();
        $table->string('known_person_in_company')->nullable();
        $table->string('known_person_relation')->nullable();
        $table->date('available_start_date')->nullable();
        $table->jsonb('source_channels')->nullable(); // multi-select checkbox
        $table->string('source_channel_other')->nullable();
        $table->string('referred_friend_name')->nullable();

        // ยินยอม
        $table->boolean('data_accuracy_consent')->default(false);
        $table->boolean('privacy_policy_consent')->default(false);
        $table->string('signature_name')->nullable();
        $table->date('signed_at')->nullable();

        // การตรวจสอบของ HR (ท้ายใบสมัคร)
        $table->boolean('hr_blacklist_checked')->nullable();
        $table->boolean('hr_criminal_checked')->nullable();
        $table->boolean('hr_social_checked')->nullable();
        $table->text('hr_verification_note')->nullable();
        $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('reviewed_at')->nullable();

        // สถานะ
        $table->enum('status', [
            'submitted', 'qualified', 'disqualified', 'code_generated',
            'awaiting_review', 'passed_test', 'failed_test',
        ])->default('submitted');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
