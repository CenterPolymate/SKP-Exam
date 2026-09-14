<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantLanguage extends Model
{
    protected $fillable = [
        'applicant_id',
        'language',
        'custom_language_name',
        'speaking_level',
        'understanding_level',
        'reading_level',
        'writing_level',
    ];

    public function applicant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }
}
