<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model
{
    protected $fillable = [
        'exam_attempt_id',
        'exam_question_id',
        'selected_choice_id',
        'selected_choice_ids',
        'answer_text',
        'answer_file_path',
        'marked_for_review',
        'score_given',
        'graded_by',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'selected_choice_ids' => 'array',
            'marked_for_review' => 'boolean',
            'score_given' => 'decimal:2',
            'graded_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'exam_question_id');
    }

    public function selectedChoice(): BelongsTo
    {
        return $this->belongsTo(ExamQuestionChoice::class, 'selected_choice_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
