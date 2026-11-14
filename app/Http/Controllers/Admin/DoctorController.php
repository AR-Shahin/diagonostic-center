<?php

namespace App\Http\Controllers\Admin;

use App\Helper\File\File;
use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    use CrudHelpers;

    public function index(Request $request)
    {
        $this->allow('webcontent-edit');

        if ($request->ajax()) {
            return $this->table(Doctor::query()->with('branch:id,code')->orderBy('sort')->orderBy('name_en'))
                ->editColumn('name_en', fn (Doctor $d) => '<div class="d-flex align-items-center gap-2">'
                    .($d->photo ? '<img src="'.e($d->photoUrl()).'" alt="" width="36" height="36" class="rounded-circle object-fit-cover">' : '<span class="rounded-circle bg-body-secondary d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px"><i class="fas fa-user-md text-muted"></i></span>')
                    .'<div><b>'.e($d->name_en).'</b><div class="small text-muted">'.e($d->qualification).'</div></div></div>')
                ->addColumn('speciality', fn (Doctor $d) => e($d->speciality_en))
                ->addColumn('schedule', fn (Doctor $d) => '<span class="small">'.e($d->scheduleText()).'</span>'.($d->branch ? ' <span class="badge text-bg-light">'.e($d->branch->code).'</span>' : ''))
                ->addColumn('website', fn (Doctor $d) => $this->statusBadge($d->show_on_website && $d->status, 'Shown', 'Hidden')
                    .($d->accepts_appointments ? '' : ' <span class="badge text-bg-light">No booking</span>'))
                ->addColumn('actions', fn (Doctor $d) => $this->rowActions('webcontent', route('admin.doctors.destroy', $d), route('admin.doctors.update', $d),
                    $d->only(['name_en', 'name_bn', 'slug', 'qualification', 'designation_en', 'designation_bn', 'speciality_en', 'speciality_bn', 'bio_en', 'bio_bn',
                        'branch_id', 'days', 'schedule_note_en', 'schedule_note_bn', 'fee', 'accepts_appointments', 'show_on_website', 'status', 'sort'])
                    + ['time_from' => substr((string) $d->time_from, 0, 5), 'time_to' => substr((string) $d->time_to, 0, 5)],
                    $d->name_en))
                ->rawColumns(['name_en', 'schedule', 'website', 'actions'])
                ->make(true);
        }

        return view('admin.doctor.index', [
            'branches' => Branch::query()->where('status', true)->orderByDesc('is_main')->pluck('name_en', 'id'),
            'days' => collect(Doctor::DAYS)->map(fn ($l) => $l[1].' · '.$l[0])->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->allow('webcontent-edit');
        $this->save(new Doctor, $request);
        $this->createdAlert();

        return back();
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $this->allow('webcontent-edit');
        $this->save($doctor, $request);
        $this->updatedAlert();

        return back();
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        $this->allow('webcontent-delete');
        $doctor->delete();
        $this->deletedAlert();

        return back();
    }

    private function save(Doctor $doctor, Request $request): void
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name_en'))]);
        $data = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('doctors')->ignore($doctor->id)],
            'qualification' => ['nullable', 'string', 'max:255'],
            'designation_en' => ['nullable', 'string', 'max:255'],
            'designation_bn' => ['nullable', 'string', 'max:255'],
            'speciality_en' => ['nullable', 'string', 'max:255'],
            'speciality_bn' => ['nullable', 'string', 'max:255'],
            'bio_en' => ['nullable', 'string', 'max:3000'],
            'bio_bn' => ['nullable', 'string', 'max:3000'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'days' => ['nullable', 'array'],
            'days.*' => ['integer', 'between:0,6'],
            'time_from' => ['nullable', 'date_format:H:i'],
            'time_to' => ['nullable', 'date_format:H:i', 'required_with:time_from'],
            'schedule_note_en' => ['nullable', 'string', 'max:255'],
            'schedule_note_bn' => ['nullable', 'string', 'max:255'],
            'fee' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:65000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ]);
        unset($data['photo']);
        $data['days'] = array_map('intval', $data['days'] ?? []);
        $data['sort'] = (int) ($data['sort'] ?? 0);
        foreach (['accepts_appointments', 'show_on_website', 'status'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }
        if ($request->hasFile('photo')) {
            File::deleteFile($doctor->photo);
            $data['photo'] = File::upload($request->file('photo'), 'doctors');
        }
        $doctor->fill($data)->save();
    }
}
