<?php

namespace App\Http\Controllers;

use App\Helper\Trait\HasAlert;
use App\Helper\Trait\HasLog;
use App\Helper\Trait\HTMLTrait;
use Yajra\DataTables\DataTables;

abstract class Controller
{
    use HasAlert,HasLog,HTMLTrait;

    public $admin_permissions = [];

    public function __construct()
    {
        if (auth('admin')->user() && method_exists(auth('admin')->user(), 'getAllPermissions')) {
            $this->admin_permissions = auth('admin')->user()->getAllPermissions()->pluck('name')->toArray();
        }
    }

    public function authorize(string $permission)
    {
        if (auth('admin')->user()) {
            if (! in_array($permission, $this->admin_permissions)) {
                abort(403);
            }
        }
    }

    public function table($query)
    {
        return DataTables::of($query)
            ->addIndexColumn();
    }

    protected function generateEditButton($row, $route)
    {
        $html = '<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#rowId_'.$row['id'].'">
        <i class="fa fa-edit"></i>
    </button>
    <div class="modal fade" id="rowId_'.$row['id'].'" tabindex="-1" role="dialog" aria-labelledby="rowId_'.$row['id'].'Label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="'.$route.'" method="post">
                <div class="modal-content text-left">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rowId_'.$row['id'].'Label">Edit '.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <input type="hidden" name="_method" value="PUT">
                        <div class="form-group">
                            <label for="name_'.$row['id'].'"><b>Name</b></label>
                            <input type="text" class="form-control" id="name_'.$row['id'].'" name="name" value="'.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>';

        return $html;
    }
}
