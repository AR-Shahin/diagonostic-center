<?php

use App\Http\Middleware\CacheClearMiddleware;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\RateLimiter;

it('sends a logged-out patient to the website login', function () {
    $this->get('/account')->assertRedirect(route('login'));
});

it('sends a logged-out admin to the admin login', function () {
    $this->get('/admin/dashboard')->assertRedirect(route('admin.login'));
});

it('answers JSON requests with 401 instead of a redirect', function () {
    $this->getJson('/account')->assertStatus(401);
});

it('does not clear the cache on every request outside local', function () {
    RateLimiter::hit('probe-key', 60);
    $this->get('/');

    expect(app()->environment())->not->toBe('local')
        ->and(RateLimiter::attempts('probe-key'))->toBe(1)
        ->and(app(Kernel::class)->hasMiddleware(CacheClearMiddleware::class))->toBeFalse();
});
