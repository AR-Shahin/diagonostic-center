<?php

function rowEditModal($row, $route)
{
    echo '
    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#rowId_'.$row->id.'">
     <i class="fa fa-edit"></i>
    </button>
    <div class="modal fade" id="rowId_'.$row->id.'" tabindex="-1" role="dialog" aria-labelledby="rowId_'.$row['id'].'Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
    <form action="'.$route.'" method="post">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="rowId_'.$row->id.'Label">Modal title</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="_token" value=" '.csrf_token().'">
            <div class="mb-3">
                <label for=""><b>Name</b></label>
                <input type="text" class="form-control" name="name" value="'.$row->name.'">
            </div>
      </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </div>
      </form>
    </div>
  </div>';

}
