<?php

namespace App\Helper\Trait;

use Illuminate\Support\Facades\Log;

trait RequestLoggerTrait
{
    protected $requestId;

    public function initializeRequestLogger(): void
    {
        $this->requestId = request()->header('X-Request-ID');
    }

    protected function log($level, $message, $context = []): void
    {
        $this->initializeRequestLogger();

        Log::log($level, $this->requestId.' '.$message, $context);
    }

    public function info($message, $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function logInfo($message, $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug($message, $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function error($message, $context = []): void
    {
        $this->log('error', $message, $context);
    }
}
