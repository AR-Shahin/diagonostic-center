<?php

namespace App\Models;

use App\Helper\Attribute\StatusAttribute;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasTranslations, SoftDeletes, StatusAttribute;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'show_on_website' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function labTests(): BelongsToMany
    {
        return $this->belongsToMany(LabTest::class);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }
}
