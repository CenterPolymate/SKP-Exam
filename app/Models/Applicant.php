<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Applicant extends Model
{
    protected $fillable = [
        'position_id',
        'expected_salary',

        // ชื่อ-นามสกุล
        'title_th',
        'first_name_th',
        'last_name_th',
        'nickname_th',
        'title_en',
        'first_name_en',
        'last_name_en',
        'nickname_en',

        // ข้อมูลส่วนตัว
        'date_of_birth',
        'nationality',
        'ethnicity',
        'religion',
        'gender',
        'height_cm',
        'weight_kg',
        'marital_status',
        'marriage_registered',

        // สุขภาพ/ประวัติ
        'has_congenital_disease',
        'congenital_disease_detail',
        'has_criminal_record',
        'criminal_record_detail',
        'has_lese_majeste_record',
        'lese_majeste_detail',

        // ที่อยู่/ติดต่อ
        'hometown',
        'country',
        'current_address',
        'phone',
        'email',
        'line_id',
        'facebook',
        'instagram',
        'twitter',

        // บัตรประชาชน
        'national_id_number',
        'national_id_back_number',
        'id_issue_date',
        'id_expiry_date',

        // ครอบครัว
        'living_status',
        'spouse_name',
        'spouse_occupation',
        'children_count',

        // บิดามารดา
        'father_name',
        'father_age',
        'father_occupation',
        'father_alive',
        'mother_name',
        'mother_age',
        'mother_occupation',
        'mother_alive',
        'siblings_count',
        'birth_order',

        'military_status',

        'language_certificate_note',

        'special_skills',
        'guarantor_required_ok',
        'shift_work_ok',
        'informal_debt_amount',
        'informal_debt_interest_rate',
        'credit_card_debt_amount',
        'total_outstanding_balance',
        'illness_or_accident_history',
        'known_person_in_company',
        'known_person_relation',
        'available_start_date',
        'source_channels',
        'source_channel_other',
        'referred_friend_name',

        'data_accuracy_consent',
        'privacy_policy_consent',
        'signature_name',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'id_issue_date' => 'date',
            'id_expiry_date' => 'date',
            'available_start_date' => 'date',
            'signed_at' => 'date',
            'reviewed_at' => 'datetime',
            'expected_salary' => 'decimal:2',
            'height_cm' => 'decimal:1',
            'weight_kg' => 'decimal:1',
            'informal_debt_amount' => 'decimal:2',
            'informal_debt_interest_rate' => 'decimal:2',
            'credit_card_debt_amount' => 'decimal:2',
            'total_outstanding_balance' => 'decimal:2',
            'marriage_registered' => 'boolean',
            'has_congenital_disease' => 'boolean',
            'has_criminal_record' => 'boolean',
            'has_lese_majeste_record' => 'boolean',
            'father_alive' => 'boolean',
            'mother_alive' => 'boolean',
            'guarantor_required_ok' => 'boolean',
            'shift_work_ok' => 'boolean',
            'data_accuracy_consent' => 'boolean',
            'privacy_policy_consent' => 'boolean',
            'hr_blacklist_checked' => 'boolean',
            'hr_criminal_checked' => 'boolean',
            'hr_social_checked' => 'boolean',
            'source_channels' => 'array',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
