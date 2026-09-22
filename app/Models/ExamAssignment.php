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
}
