<?php

namespace App\Http\Controllers\Portal\Concerns;

use App\Support\Frontend\DemoContent;
use Illuminate\Contracts\View\View;

trait RendersSitePage
{
    /** Portal pages use the public website layout, which needs $site and $content. */
    protected function page(string $view, array $data = []): View
    {
        return view($view, ['site' => DemoContent::site(), 'content' => DemoContent::all()] + $data);
    }
}
