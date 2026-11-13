<?php

namespace App\Models;

use App\Helper\Attribute\StatusAttribute;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasTranslations, StatusAttribute;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'show_on_website' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function labTests(): HasMany
    {
        return $this->hasMany(LabTest::class);
    }
}
