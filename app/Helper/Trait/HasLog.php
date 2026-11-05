<?php

namespace App\Helper\Trait;

use Illuminate\Support\Facades\Log;

trait HasLog
{
    public function logInfo($message, $context = [])
    {
        Log::info($message, $context);
    }

    public function logError($message, $context = [])
    {
        Log::error($message, $context);
    }

    public function logWarning($message, $context = [])
    {
        Log::warning($message, $context);
    }
}
