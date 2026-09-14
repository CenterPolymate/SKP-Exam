<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantWorkHistory extends Model
{
    protected $fillable = [
        'applicant_id',
        'order_no',
        'company_name',
        'position',
        'start_year',
        'end_year',
        'supervisor_name',
        'supervisor_phone',
        'last_salary',
        'resignation_reason',
    ];

    public function applicant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }
}
