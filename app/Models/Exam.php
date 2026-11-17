<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Exam extends Model
{
    protected $fillable = [
        'name',
        'description',
        'duration_minutes',
        'pass_percentage',
        'target_type',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'pass_percentage' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order_no');
    }

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'exam_position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    /**
     * คัดลอกข้อสอบทั้งชุด (คำถาม ตัวเลือก เฉลย คะแนน รูปภาพ) เป็นข้อสอบใหม่แบบฉบับร่าง
     * เพื่อให้แอดมินปรับเวลา/เกณฑ์ผ่าน/ตำแหน่งงานของชุดใหม่ได้โดยไม่ต้องสร้างคำถามซ้ำ
     *
     * ไม่คัดลอก: ตำแหน่งงานที่ผูก, ผลการสอบ (attempts) และบัญชีผู้สอบ
     */
    public function duplicate(int $createdBy): self
    {
        return DB::transaction(function () use ($createdBy): self {
            $copy = self::create([
                'name' => mb_substr($this->name, 0, 240).' (สำเนา)',
                'description' => $this->description,
                'duration_minutes' => $this->duration_minutes,
                'pass_percentage' => $this->pass_percentage,
                'target_type' => $this->target_type,
                'status' => 'draft',
                'created_by' => $createdBy,
            ]);

            foreach ($this->questions()->with('choices')->get() as $question) {
                $newQuestion = $copy->questions()->create([
                    'question_type' => $question->question_type,
                    'content_format' => $question->content_format,
                    'question_text' => $question->question_text,
                    'question_image_path' => self::copyQuestionImage($question->question_image_path),
                    'points' => $question->points,
                    'answer_format' => $question->answer_format,
                    'order_no' => $question->order_no,
                ]);

                foreach ($question->choices as $choice) {
                    $newQuestion->choices()->create([
                        'choice_text' => $choice->choice_text,
                        'is_correct' => $choice->is_correct,
                        'order_no' => $choice->order_no,
                    ]);
                }
            }

            return $copy;
        });
    }

    /** คัดลอกไฟล์รูปโจทย์เป็นไฟล์ใหม่ เพื่อให้ข้อสอบแต่ละชุดไม่ใช้ไฟล์ร่วมกัน */
    private static function copyQuestionImage(?string $path): ?string
    {
        if ($path === null || ! Storage::disk('public')->exists($path)) {
            return $path;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $newPath = trim(dirname($path), '.\\/').'/'.Str::random(40).($extension !== '' ? '.'.$extension : '');
        $newPath = ltrim($newPath, '/');

        Storage::disk('public')->copy($path, $newPath);

        return $newPath;
    }
}
