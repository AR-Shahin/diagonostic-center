<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    use CrudHelpers;

    public function index(Request $request)
    {
        $this->allow('webcontent-edit');

        if ($request->ajax()) {
            return $this->table(Testimonial::query()->orderBy('sort')->latest('id'))
                ->editColumn('name', fn (Testimonial $t) => '<b>'.e($t->name).'</b><div class="small text-muted">'.e($t->meta_bn ?: $t->meta_en).'</div>')
                ->addColumn('text', fn (Testimonial $t) => '<span class="small">'.e(str($t->text_bn ?: $t->text_en)->limit(110)).'</span>')
                ->addColumn('rating', fn (Testimonial $t) => str_repeat('★', $t->rating).'<span class="text-muted">'.str_repeat('☆', 5 - $t->rating).'</span>')
                ->addColumn('state', fn (Testimonial $t) => ! $t->consent ? '<span class="badge text-bg-warning">No consent · hidden</span>' : $this->statusBadge($t->status, 'Shown', 'Hidden'))
                ->addColumn('actions', fn (Testimonial $t) => $this->rowActions('webcontent', route('admin.testimonials.destroy', $t), route('admin.testimonials.update', $t),
                    $t->only(['name', 'meta_bn', 'meta_en', 'text_bn', 'text_en', 'rating', 'consent', 'consent_note', 'status', 'sort']), $t->name))
                ->rawColumns(['name', 'text', 'rating', 'state', 'actions'])
                ->make(true);
        }

        return view('admin.testimonial.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->allow('webcontent-edit');
        $this->save(new Testimonial, $request);
        $this->createdAlert();

        return back();
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $this->allow('webcontent-edit');
        $this->save($testimonial, $request);
        $this->updatedAlert();

        return back();
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->allow('webcontent-delete');
        $testimonial->delete();
        $this->deletedAlert();

        return back();
    }

    private function save(Testimonial $testimonial, Request $request): void
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'meta_bn' => ['nullable', 'string', 'max:100'],
            'meta_en' => ['nullable', 'string', 'max:100'],
            'text_bn' => ['required_without:text_en', 'nullable', 'string', 'max:1000'],
            'text_en' => ['nullable', 'string', 'max:1000'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'consent_note' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:65000'],
        ]);
        $data['sort'] = (int) ($data['sort'] ?? 0);
        $data['consent'] = $request->boolean('consent');
        $data['status'] = $request->boolean('status');
        $testimonial->fill($data)->save();
    }
}
