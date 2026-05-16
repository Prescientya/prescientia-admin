<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuidelineSection extends Model
{
    protected $fillable = ['guideline_page_id', 'title', 'description', 'order'];

    public function page(): BelongsTo
    {
        return $this->belongsTo(GuidelinePage::class, 'guideline_page_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GuidelineItem::class, 'guideline_section_id')->orderBy('order');
    }
}
