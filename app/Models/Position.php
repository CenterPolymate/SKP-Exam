<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    // ลบแบบ soft delete เพื่อให้ใบสมัครเดิมยังอ้างอิงตำแหน่งนี้ได้
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }
}
