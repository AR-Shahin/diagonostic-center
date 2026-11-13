<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Support\Frontend\DemoContent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PageController extends Controller
{
    public function home()
    {
        return $this->page('frontend.home');
    }

    public function about()
    {
        return $this->page('frontend.pages.about');
    }

    public function tests(Request $request)
    {
        return $this->page('frontend.pages.tests', [
            'q' => trim((string) $request->query('q', '')),
            'cat' => (string) $request->query('cat', ''),
        ]);
    }

    public function packages()
    {
        return $this->page('frontend.pages.packages');
    }

    public function doctors()
    {
        return $this->page('frontend.pages.doctors');
    }

    public function services()
    {
        return $this->page('frontend.pages.services');
    }

    public function contact()
    {
        return $this->page('frontend.pages.contact');
    }

    public function appointment(Request $request)
    {
        return $this->page('frontend.pages.appointment', [
            'selectedTest' => (string) $request->query('test', ''),
        ]);
    }

    public function report()
    {
        return $this->page('frontend.pages.report');
    }

    public function sitemap(): Response
    {
        $routes = ['home', 'about', 'tests', 'packages', 'doctors', 'services', 'contact', 'appointment', 'report'];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($routes as $name) {
            $xml .= '  <url><loc>'.e(route('site.'.$name)).'</loc></url>'."\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Every public page gets the site settings ($site) and the page content ($content).
     * When the admin panel manages this content, swap DemoContent for real queries here.
     */
    private function page(string $view, array $data = [])
    {
        return view($view, ['site' => DemoContent::site(), 'content' => DemoContent::all()] + $data);
    }
}
