<?php

namespace App\Models;

use App\Helper\Attribute\StatusAttribute;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class SampleType extends Model
{
    use HasTranslations, StatusAttribute;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }
}
