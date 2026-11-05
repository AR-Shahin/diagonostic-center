<?php

namespace App\Models;

use App\Helper\Attribute\StatusAttribute;
use App\Helper\Mutator\SlugMutator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory,SlugMutator,StatusAttribute;

    protected $guarded = [];
}
