<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantEducation extends Model
{
    // "education" is uncountable, so Eloquent's auto-pluralized guess
    // ("applicant_education") doesn't match the migration's table name.
    protected $table = 'applicant_educations';

    protected $fillable = [
        'applicant_id',
        'degree_level',
        'institution_name',
        'province_country',
        'start_year',
        'end_year',
        'major',
        'gpa',
    ];

    public function applicant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }
}
