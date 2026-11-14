<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Branch;
use App\Models\Doctor;
use App\Support\Frontend\DemoContent;
use App\Support\Frontend\SiteContent;
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
            'selectedDoctor' => (string) $request->query('doctor', ''),
            'branchOptions' => Branch::query()->where('status', true)->orderByDesc('is_main')->orderBy('sort')->get()
                ->mapWithKeys(fn (Branch $b) => [$b->id => $b->localized('name')])->all(),
            'doctorOptions' => Doctor::query()->forWebsite()->where('accepts_appointments', true)->orderBy('sort')->orderBy('name_en')->get()
                ->map(fn (Doctor $d) => ['slug' => $d->slug, 'label' => $d->localized('name').($d->localized('speciality') ? ' — '.$d->localized('speciality') : '')])->all(),
        ]);
    }

    public function report()
    {
        return $this->page('frontend.pages.report');
    }

    public function articles()
    {
        return $this->page('frontend.pages.articles', ['articles' => SiteContent::articles()]);
    }

    public function article(string $slug)
    {
        $article = Article::query()->published()->where('slug', $slug)->firstOrFail();

        return $this->page('frontend.pages.article', [
            'article' => SiteContent::article($article),
            'more' => collect(SiteContent::articles(4))->where('slug', '!=', $slug)->take(3)->all(),
        ]);
    }

    public function sitemap(): Response
    {
        $routes = ['home', 'about', 'tests', 'packages', 'doctors', 'services', 'contact', 'appointment', 'report', 'articles'];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($routes as $name) {
            $xml .= '  <url><loc>'.e(route('site.'.$name)).'</loc></url>'."\n";
        }
        foreach (Article::query()->published()->get(['slug', 'updated_at']) as $a) {
            $xml .= '  <url><loc>'.e(route('site.article', $a->slug)).'</loc><lastmod>'.$a->updated_at->toAtomString().'</lastmod></url>'."\n";
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
