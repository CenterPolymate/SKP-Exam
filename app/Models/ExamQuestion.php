<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamQuestion extends Model
{
    protected $fillable = [
        'exam_id',
        'question_type',
        'content_format',
        'question_text',
        'question_image_path',
        'points',
        'answer_format',
        'order_no',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function choices(): HasMany
    {
        return $this->hasMany(ExamQuestionChoice::class)->orderBy('order_no');
    }
}
