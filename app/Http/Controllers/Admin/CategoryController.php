<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Repositories\BaseCRUDRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    private $repository;

    private $table = 'categories';

    public function __construct()
    {
        parent::__construct();
        $this->repository = new BaseCRUDRepository(new Category);
    }

    public function index(Request $request)
    {

        if ($request->ajax()) {

            return $this->table($this->repository->query())
                ->addIndexColumn()
                ->addColumn('actions', function ($row) {
                    $deleteRoute = route('admin.categories.destroy', $row['id']);
                    $editRoute = route('admin.categories.update', $row['id']);
                    $html = $this->generateEditButton($row, $editRoute).$this->generateDeleteButton($row, $deleteRoute, 'admin-delete', 'DELETE');

                    return $html;
                })

                ->addColumn('status', fn ($row) => $row->status_badge)

                ->rawColumns(['actions', 'status'])
                ->make(true);
        }

        return view('admin.category.index');
    }

    public function destroy($category)
    {
        if ($this->repository->delete($category)) {
            $this->deletedAlert();

            return back();
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', "unique:$this->table,name"],
        ]);

        if (
            $this->repository->store([
                'name' => $request->name,
                'slug' => $request->name,
            ])
        ) {
            $this->createdAlert();

            return back();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', Rule::unique($this->table)->ignore($id)],
        ]);
        if (
            $this->repository->update($id, [
                'name' => $request->name,
                'slug' => $request->name,
            ])
        ) {
            $this->updatedAlert();

            return back();
        }
    }
}
