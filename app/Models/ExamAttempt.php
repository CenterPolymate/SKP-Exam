<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_assignment_id',
        'exam_id',
        'started_at',
        'submitted_at',
        'status',
        'score',
        'suspicious_activity_count',
        'suspicious_activity_log',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score' => 'decimal:2',
            'suspicious_activity_log' => 'array',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ExamAssignment::class, 'exam_assignment_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class, 'exam_attempt_id');
    }
}
