<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuidelinePage extends Model
{
    protected $fillable = ['user_type', 'title', 'subtitle', 'is_published'];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(GuidelineSection::class, 'guideline_page_id')->orderBy('order');
    }

    public function getRouteKeyName(): string
    {
        return 'user_type';
    }

    public function getUserTypeLabelAttribute(): string
    {
        return $this->user_type === 'siswa' ? 'Siswa' : 'Guru';
    }
}
