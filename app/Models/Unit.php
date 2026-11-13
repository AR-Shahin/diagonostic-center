<?php

namespace App\Models;

use App\Helper\Attribute\StatusAttribute;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use StatusAttribute;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }
}
