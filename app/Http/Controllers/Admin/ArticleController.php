<?php

namespace App\Http\Controllers\Admin;

use App\Helper\File\File;
use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Support\SafeHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    use CrudHelpers;

    public function index(Request $request)
    {
        $this->allow('webcontent-edit');

        if ($request->ajax()) {
            return $this->table(Article::query()->latest('published_at')->latest('id'))
                ->editColumn('title_bn', fn (Article $a) => '<b>'.e($a->title_bn ?: $a->title_en).'</b><div class="small text-muted">'.e($a->title_en).'</div>')
                ->addColumn('category', fn (Article $a) => e($a->category_bn ?: $a->category_en))
                ->addColumn('state', fn (Article $a) => '<span class="badge text-bg-'.['Draft' => 'secondary', 'Scheduled' => 'info', 'Published' => 'success'][$a->stateLabel()].'">'.$a->stateLabel().'</span>'
                    .($a->published_at ? '<div class="small text-muted">'.$a->published_at->format('d M Y').'</div>' : ''))
                ->addColumn('actions', fn (Article $a) => ($a->stateLabel() === 'Published' ? '<a class="btn btn-sm btn-light me-1" target="_blank" href="'.route('site.article', $a->slug).'" title="View on website"><i class="fas fa-external-link-alt"></i></a>' : '')
                    .$this->rowActions('webcontent', route('admin.articles.destroy', $a), route('admin.articles.update', $a),
                        $a->only(['title_bn', 'title_en', 'slug', 'category_bn', 'category_en', 'excerpt_bn', 'excerpt_en', 'body_bn', 'body_en'])
                        + ['published_at' => $a->published_at?->format('Y-m-d\TH:i')], $a->title_en ?: $a->title_bn))
                ->rawColumns(['title_bn', 'state', 'actions'])
                ->make(true);
        }

        return view('admin.article.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->allow('webcontent-edit');
        $this->save(new Article(['created_by' => auth('admin')->id()]), $request);
        $this->createdAlert();

        return back();
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $this->allow('webcontent-edit');
        $this->save($article, $request);
        $this->updatedAlert();

        return back();
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->allow('webcontent-delete');
        $article->delete();
        $this->deletedAlert();

        return back();
    }

    private function save(Article $article, Request $request): void
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: ($request->input('title_en') ?: Str::random(8)))]);
        $data = $request->validate([
            'title_bn' => ['required_without:title_en', 'nullable', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('articles')->ignore($article->id)],
            'category_bn' => ['nullable', 'string', 'max:100'],
            'category_en' => ['nullable', 'string', 'max:100'],
            'excerpt_bn' => ['nullable', 'string', 'max:300'],
            'excerpt_en' => ['nullable', 'string', 'max:300'],
            'body_bn' => ['nullable', 'string', 'max:60000'],
            'body_en' => ['nullable', 'string', 'max:60000'],
            'published_at' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        unset($data['image']);
        $data['body_bn'] = SafeHtml::clean($data['body_bn'] ?? null);
        $data['body_en'] = SafeHtml::clean($data['body_en'] ?? null);
        if ($request->hasFile('image')) {
            File::deleteFile($article->image);
            $data['image'] = File::upload($request->file('image'), 'articles');
        }
        $article->fill($data)->save();
    }
}
