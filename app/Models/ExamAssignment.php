<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAssignment extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $fillable = [
        'applicant_id',
        'username',
        'password_encrypted',
        'exam_deadline',
        'created_by',
    ];

    protected $hidden = [
        'password_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'exam_deadline' => 'date',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function isExpired(): bool
    {
        return $this->exam_deadline !== null
            && now()->toDateString() > $this->exam_deadline->toDateString();
    }

    public function markApplicantFailedIfExpired(): void
    {
        if (! $this->isExpired()) {
            return;
        }

        // เปลี่ยนสถานะเป็น "ไม่ผ่านการทดสอบ" เฉพาะกรณีที่ยังไม่ได้ส่งข้อสอบทันเวลา
        // (ถ้าส่งครบแล้วรอ HR ตรวจ หรือตรวจผลแล้ว ต้องไม่ไปทับสถานะเดิม)
        if ($this->applicant && $this->applicant->status === 'code_generated') {
            $this->applicant->update(['status' => 'failed_test']);
        }
    }
}
