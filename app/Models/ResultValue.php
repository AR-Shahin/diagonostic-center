<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultValue extends Model
{
    protected $guarded = ['id'];

    public function result(): BelongsTo
    {
        return $this->belongsTo(TestResult::class, 'test_result_id');
    }
}
