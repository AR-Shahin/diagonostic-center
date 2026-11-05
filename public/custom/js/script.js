function initalizeDatatable(route,columns,table="data-table"){
    $(function () {
        $('.'+table).DataTable({
            "processing": true,
            "retrieve": true,
            "serverSide": true,
            'paginate': true,
            'searchDelay': 700,
            "bDeferRender": true,
            "responsive": true,
            "autoWidth": true,
            "order": [ [0, 'asc'] ],
            lengthMenu: [[5,10,15, 25, 50, -1], [5,10,15, 25, 50, "All"]],
            pageLength: 15,
            ajax: route,
            columns: columns
        });
      });
}

$(function() {
    // Move all statically rendered modals to the body
    $('.modal').addClass('static-modal').appendTo('body');
});

// Handle dynamically generated modals from DataTables
$(document).on('preDraw.dt', function() {
    // Before DataTables redraws, clean up any previously moved dynamic modals 
    // to prevent duplicate IDs which cause the modal to "blink" or fail.
    $('.dt-moved-modal').remove();
});

$(document).on('draw.dt', function () {
    // After DataTables draws, find new modals inside the table and move them to the body
    $('.data-table .modal').addClass('dt-moved-modal').appendTo('body');
});
