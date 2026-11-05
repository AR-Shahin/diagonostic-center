@extends("admin.layouts.master")

@section("title","Assign Permission")

@section("master_content")

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
    
    <!-- Premium Header -->
    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
            <div>
                <h3 class="fw-bold mb-0 text-dark">
                    Assign Permissions to <span class="text-primary">{{ $role->name }}</span>
                </h3>
                <p class="text-muted small mt-1 mb-0"><i class="fas fa-shield-alt me-1"></i> Configure access rights and capabilities.</p>
            </div>
            <div>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-arrow-left me-1"></i> <span class="d-none d-sm-inline">Back to Roles</span><span class="d-inline d-sm-none">Back</span></a>
            </div>
        </div>
    </div>

    <div class="card-body px-0 pt-0">
        
        <!-- Sticky Toolbar -->
        <div class="sticky-top bg-white px-4 py-3 border-bottom shadow-sm" style="z-index: 100; top: 0; backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.9) !important;">
            <div class="row align-items-center gy-3">
                <div class="col-12 col-xl-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 rounded-start-pill ps-3 text-muted">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="moduleSearch" class="form-control bg-light border-0 rounded-end-pill py-2" placeholder="Search modules (e.g. Users, Roles...)" style="box-shadow: none;">
                    </div>
                </div>
                <div class="col-12 col-xl-7 text-xl-end text-start">
                    <span class="text-muted small fw-bold me-2 text-uppercase d-none d-xl-inline-block">Bulk Actions:</span>
                    <div class="d-flex flex-wrap gap-2 justify-content-start justify-content-xl-end" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm global-select px-3 rounded-pill flex-grow-1 flex-sm-grow-0" data-action="view"><i class="fas fa-eye me-1"></i> View All</button>
                        <button type="button" class="btn btn-outline-success btn-sm global-select px-3 rounded-pill flex-grow-1 flex-sm-grow-0" data-action="create"><i class="fas fa-plus me-1"></i> Create All</button>
                        <button type="button" class="btn btn-outline-info btn-sm global-select px-3 rounded-pill flex-grow-1 flex-sm-grow-0" data-action="edit"><i class="fas fa-edit me-1"></i> Edit All</button>
                        <button type="button" class="btn btn-outline-danger btn-sm global-select px-3 rounded-pill flex-grow-1 flex-sm-grow-0" data-action="delete"><i class="fas fa-trash-alt me-1"></i> Delete All</button>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route("admin.roles.assign__permission", $role->id) }}" method="post" id="permissionForm">
            @csrf
            
            <div class="module-container px-4 py-3 bg-light" style="min-height: 400px;">
                @foreach ($modules as $moduleName => $permissions)
                
                <div class="module-row card border-0 shadow-sm rounded-4 mb-3 transition-all hover-elevate" data-name="{{ strtolower($moduleName) }}">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            
                            <!-- Module Title & Toggle -->
                            <div class="col-md-3 border-end-md pe-md-4 mb-3 mb-md-0 pb-3 pb-md-0 border-bottom-md-none border-bottom">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 fw-bolder text-dark text-uppercase" style="letter-spacing: 0.5px; font-size: 0.95rem;">
                                        <i class="fas fa-cube text-primary me-2 opacity-75"></i> {{ $moduleName }}
                                    </h6>
                                    <div class="form-check form-switch ms-3 m-0">
                                        <input type="checkbox" class="form-check-input select-all-module" id="all_{{ $moduleName }}" data-module="{{ $moduleName }}" style="cursor: pointer; transform: scale(1.2);">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Permission Chips -->
                            <div class="col-md-9 ps-md-4">
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($permissions as $permission)
                                    <div class="permission-chip">
                                        <input type="checkbox" class="d-none permission-input-{{ $moduleName }} action-{{ $permission['action'] }}" 
                                                id="p_{{ $permission['id'] }}" 
                                                name="permissions[]" 
                                                value="{{ $permission['id'] }}"
                                                {{ in_array($permission['id'], $alreadyGiven) ? 'checked' : '' }}
                                                data-module="{{ $moduleName }}"
                                                data-action="{{ $permission['action'] }}">
                                        
                                        <label for="p_{{ $permission['id'] }}" class="chip-label px-2 py-1 rounded-pill small fw-bold transition-all border d-flex align-items-center justify-content-center m-0" style="font-size: 0.8rem;">
                                            @if($permission['action'] == 'view') <i class="fas fa-eye me-1 opacity-50" style="font-size: 0.75rem;"></i>
                                            @elseif($permission['action'] == 'create') <i class="fas fa-plus me-1 opacity-50" style="font-size: 0.75rem;"></i>
                                            @elseif($permission['action'] == 'edit') <i class="fas fa-edit me-1 opacity-50" style="font-size: 0.75rem;"></i>
                                            @elseif($permission['action'] == 'delete') <i class="fas fa-trash-alt me-1 opacity-50" style="font-size: 0.75rem;"></i>
                                            @else <i class="fas fa-check me-1 opacity-50" style="font-size: 0.75rem;"></i> @endif
                                            
                                            {{ strtoupper($permission['action']) }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
                
                @endforeach
            </div>

            <!-- Floating Action Bar (Sticky Footer) -->
            <div class="sticky-footer bg-white border-top shadow-lg p-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3" style="position: sticky; bottom: 0; z-index: 101;">
                <div class="d-flex align-items-center w-100">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3 flex-shrink-0" style="width: 45px; height: 45px;">
                        <i class="fas fa-check-double fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Selection Summary</h6>
                        <small class="text-muted"><span id="totalSelectedCount" class="fw-bold text-dark fs-6">0</span> permissions granted</small>
                    </div>
                </div>
                <div class="w-100 text-sm-end">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm d-flex justify-content-center align-items-center transition-all hover-elevate sync-btn w-100">
                        <i class="fas fa-sync-alt me-2"></i> SYNC PERMISSIONS
                    </button>
                </div>
            </div>
            
        </form>

    </div>
</div>

@stop

@push("css")
<style>
    /* Premium Transitions */
    .transition-all { transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
    .hover-elevate:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important; }
    
    /* Layout Helpers */
    @media (min-width: 768px) {
        .border-end-md { border-right: 1px solid #e9ecef; }
        .border-bottom-md-none { border-bottom: none !important; }
    }
    
    @media (min-width: 576px) {
        .sync-btn {
            max-width: 220px;
            display: inline-flex !important;
        }
    }

    /* Modern Chip Styling */
    .permission-chip { display: inline-block; }
    .chip-label { 
        background-color: #fff; 
        color: #6c757d; 
        border-color: #dee2e6 !important;
        cursor: pointer;
        user-select: none;
    }
    .chip-label:hover {
        background-color: #f8f9fa;
        border-color: #ced4da !important;
    }
    
    /* Dynamic Active States based on Action */
    .permission-chip input:checked + .chip-label {
        color: #fff;
        border-color: transparent !important;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transform: scale(1.05);
    }
    
    /* Action-specific Colors */
    .permission-chip input.action-view:checked + .chip-label { background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%); }
    .permission-chip input.action-create:checked + .chip-label { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .permission-chip input.action-edit:checked + .chip-label { background: linear-gradient(135deg, #00B4DB 0%, #0083B0 100%); }
    .permission-chip input.action-delete:checked + .chip-label { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }
    .permission-chip input:checked + .chip-label { background: linear-gradient(135deg, #434343 0%, #000000 100%); /* fallback */ }

    /* Custom Checkbox Switch Enhancement */
    .form-switch .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    /* Active State for Bulk Buttons */
    .btn-outline-primary.active { box-shadow: inset 0 3px 5px rgba(0,0,0,0.125); }
    .btn-outline-success.active { box-shadow: inset 0 3px 5px rgba(0,0,0,0.125); }
    .btn-outline-info.active { box-shadow: inset 0 3px 5px rgba(0,0,0,0.125); }
    .btn-outline-danger.active { box-shadow: inset 0 3px 5px rgba(0,0,0,0.125); }
</style>
@endpush

@push("script")
    <script>
    $(document).ready(function() {
        // Initialize count on load
        updateTotalCount();
        
        // Initialize Select All toggles on load based on checked children
        $('.module-row').each(function() {
            const moduleName = $(this).data('name');
            // Since data-module is exactly case-preserved from php, we can find it via first input
            const actualModuleName = $(this).find('input[name="permissions[]"]').first().data('module');
            if(actualModuleName) {
                const total = $(`.permission-input-${actualModuleName}`).length;
                const checked = $(`.permission-input-${actualModuleName}:checked`).length;
                if(total > 0 && total === checked) {
                    $(`#all_${actualModuleName}`).prop('checked', true);
                }
            }
        });

        // Search Filter (Animated)
        $('#moduleSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('.module-row').each(function() {
                const isMatch = $(this).data('name').indexOf(value) > -1;
                if (isMatch) {
                    $(this).fadeIn(200);
                } else {
                    $(this).fadeOut(200);
                }
            });
        });

        // Module-specific Select All
        $('.select-all-module').on('change', function() {
            const moduleName = $(this).data('module');
            const isChecked = $(this).prop('checked');
            
            // Only trigger change if the value actually changes to avoid infinite loops,
            // but here trigger change is needed for the updateTotalCount
            $(`.permission-input-${moduleName}`).prop('checked', isChecked);
            updateTotalCount();
        });

        // Global Action Select (View All, Create All, etc)
        $('.global-select').on('click', function() {
            const action = $(this).data('action');
            // Target inputs where the parent module-row is visible (to respect search filter)
            const visibleModules = $('.module-row:visible');
            const targetInputs = visibleModules.find(`.action-${action}`);
            
            if (targetInputs.length === 0) return;
            
            const allChecked = targetInputs.length === targetInputs.filter(':checked').length;
            
            targetInputs.prop('checked', !allChecked);
            updateTotalCount();
            
            // Re-evaluate module level switches
            visibleModules.each(function() {
                const actualModuleName = $(this).find('input[name="permissions[]"]').first().data('module');
                if(actualModuleName) {
                    const total = $(`.permission-input-${actualModuleName}`).length;
                    const checked = $(`.permission-input-${actualModuleName}:checked`).length;
                    $(`#all_${actualModuleName}`).prop('checked', total === checked);
                }
            });
            
            // Visual feedback for the button
            if (!allChecked) {
                $(this).addClass('active bg-' + getActionColor(action) + ' text-white');
                $(this).removeClass('btn-outline-' + getActionColor(action));
            } else {
                $(this).removeClass('active bg-' + getActionColor(action) + ' text-white');
                $(this).addClass('btn-outline-' + getActionColor(action));
            }
        });
        
        function getActionColor(action) {
            switch(action) {
                case 'view': return 'primary';
                case 'create': return 'success';
                case 'edit': return 'info';
                case 'delete': return 'danger';
                default: return 'secondary';
            }
        }

        // Sync Individual Checkboxes to Module Select All
        $('input[name="permissions[]"]').on('change', function() {
            updateTotalCount();
            const moduleName = $(this).data('module');
            const total = $(`.permission-input-${moduleName}`).length;
            const checked = $(`.permission-input-${moduleName}:checked`).length;
            $(`#all_${moduleName}`).prop('checked', total > 0 && total === checked);
        });

        // Throttle count updates if there are many checkboxes
        function updateTotalCount() {
            const count = $('input[name="permissions[]"]:checked').length;
            // Animate count change
            $('#totalSelectedCount').prop('Counter', $('#totalSelectedCount').text()).animate({
                Counter: count
            }, {
                duration: 200,
                easing: 'swing',
                step: function (now) {
                    $(this).text(Math.ceil(now));
                }
            });
        }
    });
    </script>
@endpush
