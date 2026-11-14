<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasTranslations;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['consent' => 'boolean', 'status' => 'boolean'];
    }

    /** Only reviews the patient agreed to publish ever reach the website. */
    public function scopeForWebsite(Builder $query): Builder
    {
        return $query->where('status', true)->where('consent', true);
    }
}
