<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GuidelineItem extends Model
{
    protected $fillable = ['guideline_section_id', 'title', 'content', 'image_path', 'order'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(GuidelineSection::class, 'guideline_section_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }
}
