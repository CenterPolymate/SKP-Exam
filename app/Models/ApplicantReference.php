<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantReference extends Model
{
    protected $fillable = [
        'applicant_id',
        'order_no',
        'name',
        'relationship',
        'workplace_address',
        'position',
        'phone',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }
}
