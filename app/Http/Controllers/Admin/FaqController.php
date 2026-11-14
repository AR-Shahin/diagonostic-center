<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use CrudHelpers;

    public function index(Request $request)
    {
        $this->allow('webcontent-edit');

        if ($request->ajax()) {
            return $this->table(Faq::query()->orderBy('sort')->orderBy('id'))
                ->editColumn('question_bn', fn (Faq $f) => '<b>'.e($f->question_bn).'</b><div class="small text-muted">'.e($f->question_en).'</div>')
                ->addColumn('state', fn (Faq $f) => $this->statusBadge($f->status, 'Shown', 'Hidden'))
                ->addColumn('actions', fn (Faq $f) => $this->rowActions('webcontent', route('admin.faqs.destroy', $f), route('admin.faqs.update', $f),
                    $f->only(['question_bn', 'question_en', 'answer_bn', 'answer_en', 'sort', 'status']), 'question'))
                ->rawColumns(['question_bn', 'state', 'actions'])
                ->make(true);
        }

        return view('admin.faq.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->allow('webcontent-edit');
        $this->save(new Faq, $request);
        $this->createdAlert();

        return back();
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $this->allow('webcontent-edit');
        $this->save($faq, $request);
        $this->updatedAlert();

        return back();
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $this->allow('webcontent-delete');
        $faq->delete();
        $this->deletedAlert();

        return back();
    }

    private function save(Faq $faq, Request $request): void
    {
        $data = $request->validate([
            'question_bn' => ['required_without:question_en', 'nullable', 'string', 'max:255'],
            'question_en' => ['nullable', 'string', 'max:255'],
            'answer_bn' => ['required_with:question_bn', 'nullable', 'string', 'max:3000'],
            'answer_en' => ['required_with:question_en', 'nullable', 'string', 'max:3000'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:65000'],
        ]);
        $data['sort'] = (int) ($data['sort'] ?? 0);
        $data['status'] = $request->boolean('status');
        $faq->fill($data)->save();
    }
}
