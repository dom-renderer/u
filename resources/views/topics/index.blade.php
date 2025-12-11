@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
/* Hierarchical Tree Styles */
.tree-view .tree-indent {
    display: inline-block;
}

.tree-view .tree-indent-0 { margin-left: 0px; }
.tree-view .tree-indent-1 { margin-left: 25px; }
.tree-view .tree-indent-2 { margin-left: 50px; }
.tree-view .tree-indent-3 { margin-left: 75px; }
.tree-view .tree-indent-4 { margin-left: 100px; }

.tree-view .tree-toggle {
    cursor: pointer;
    margin-right: 8px;
    color: #6c757d;
    width: 16px;
    display: inline-block;
    text-align: center;
    transition: all 0.2s;
}

.tree-view .tree-toggle:hover {
    color: #007bff;
}

.tree-view .category-name {
    font-weight: 500;
}

.tree-view .parent-category {
    font-weight: 600;
    color: #495057;
}

.tree-view .child-category {
    color: #6c757d;
}

.tree-view .tree-line {
    position: relative;
}

.tree-view .tree-line::before {
    content: '';
    position: absolute;
    left: 12px;
    top: -12px;
    bottom: 50%;
    border-left: 1px dashed #dee2e6;
}

.tree-view .tree-line::after {
    content: '';
    position: absolute;
    left: 12px;
    top: 50%;
    width: 13px;
    border-top: 1px dashed #dee2e6;
}

.tree-view .collapsed {
    display: none !important;
}

.view-toggle-buttons {
    margin-bottom: 15px;
}

.view-toggle-buttons .btn {
    margin-right: 10px;
}

.tree-controls {
    margin-bottom: 10px;
    display: none;
}

.tree-controls.show {
    display: block;
}

/* Table hover effect for tree view */
.tree-view tbody tr:hover {
    background-color: #f8f9fa;
}

/* Active view button styling */
.view-toggle-buttons .btn.active {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}
</style>
@endpush

@section('content')

    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }} </h1>
        <div class="lead">
            {{ $page_description }}
            @if (auth()->user()->can('topics.create'))
                <a href="{{ route('topics.create') }}" class="btn btn-primary btn-sm float-end">Add Category</a>
            @endif
                <button type="button" class="btn btn-primary btn-sm btn-sort float-end" name="operation" value="sort" style="margin-right:20px;"> Sort Categories </button>
        </div>
        
        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <!-- View Toggle Buttons -->
        <div class="view-toggle-buttons">
            <button type="button" class="btn btn-outline-secondary active" id="table-view-btn" onclick="switchView('table')">
                <i class="fas fa-table"></i> Table View
            </button>
            <button type="button" class="btn btn-outline-secondary" id="tree-view-btn" onclick="switchView('tree')">
                <i class="fas fa-sitemap"></i> Tree View
            </button>
        </div>

        <!-- Tree Controls (only shown in tree view) -->
        <div class="tree-controls" id="tree-controls">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllCategories(false)">
                <i class="fas fa-compress-alt"></i> Collapse All
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllCategories(true)">
                <i class="fas fa-expand-alt"></i> Expand All
            </button>
        </div>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                <table class="table table-striped" id="category-table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Parent</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        

    </div> 
@endsection


@push('js')
<script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>

<script>
    let usersTable;
    let currentView = 'table';
    
    $(document).ready(function($){

        initializeDataTable();

        $(document).on('click', '.status-changer', function(e) {
            e.preventDefault();
            Swal.fire({
                title: $(this).attr('data-description'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: $(this).attr('data-blable')
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).parents('form').submit();
                    return true;
                } else {
                    return false;
                }
            })
        });

        $(document).on('click', '.deleteGroup', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this Topic?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).parents('form').submit();
                    return true;
                } else {
                    return false;
                }
            })
        });

        $(document).on('click', '.tree-toggle', function(e) {
            e.preventDefault();
            const categoryId = $(this).data('category-id');
            toggleChildren(categoryId);
        });

        function gatherRowData(that) {
            let allData = [];
            
            return new Promise(function(resolve, reject) {

                $(that).find("tr").each(function(index, el) {
                    var catId = $(el).attr('data-catid');
                    if (catId) {
                        allData.push(catId);
                    }
                });
                
                resolve(allData);
            });
        }

        $(".btn-sort").click(function() {
            alert("Drag and drop the categories to change their order.")
            $("#category-table").sortable({
                items: '.sortableTR',
                cursor: 'move',
                axis: 'y',
                dropOnEmpty: false,
                start: function(e, ui) {
                    ui.item.addClass("selected");
                },
                stop: function(e, ui) {
                    ui.item.removeClass("selected");

                    gatherRowData(this).then(function(allData) {

                        $.ajax({
                            url: "{{ route('sort-categories') }}",
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                cat_ids: allData
                            },
                            success: function(response) {
                            }
                        });
                    }).catch(function(error) {

                    });
                }
            });
        });        
        
    });

    function initializeDataTable() {
        if (usersTable) {
            usersTable.destroy();
        }

        usersTable = new DataTable('#category-table', {
            ajax: {
                url: "{{ route('topics.index') }}",
                data: function ( d ) {
                    return $.extend( {}, d, {
                        view_type: currentView
                    });
                }
            },
            "aLengthMenu": [[50, 100, -1], [50, 100, 'All']],
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                 { data: 'id', searchable: false },
                 { data: 'name' },
                 { data: 'parentcat', searchable: false },
                 { data: 'status'},
                 { data: 'action', searchable: false }
            ],
            initComplete: function(settings) {
                if (currentView === 'tree') {
                    $('#category-table').addClass('tree-view');
                }
            },
            createdRow: function(row, data, dataIndex) {
                $(row).attr('data-exclude', 'true')
                .attr('data-catid', data.id)
                .addClass('sortableTR');

                if (currentView === 'tree' && data.tree_data) {
                    $(row).attr('data-level', data.tree_data.level)
                           .attr('data-parent-id', data.tree_data.parent_id || '')
                           .addClass(data.tree_data.level > 0 ? 'child-row' : 'parent-row');
                }
            }
        });
    }

    function switchView(viewType) {
        currentView = viewType;
        
        $('.view-toggle-buttons .btn').removeClass('active');
        if (viewType === 'table') {
            $('#table-view-btn').addClass('active');
            $('#tree-controls').removeClass('show');
            $('#category-table').removeClass('tree-view');
        } else {
            $('#tree-view-btn').addClass('active');
            $('#tree-controls').addClass('show');
            $('#category-table').addClass('tree-view');
        }
        
        initializeDataTable();
    }

    function toggleChildren(parentId) {
        const parentRow = $(`[data-catid="${parentId}"]`);
        const childRows = $(`[data-parent-id="${parentId}"]`);
        const toggleIcon = parentRow.find('.tree-toggle');
        
        if (childRows.hasClass('collapsed')) {
            childRows.removeClass('collapsed');
            toggleIcon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
        } else {
            childRows.addClass('collapsed');
            toggleIcon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
            
            childRows.each(function() {
                const childId = $(this).attr('data-catid');
                const nestedChildren = $(`[data-parent-id="${childId}"]`);
                nestedChildren.addClass('collapsed');
            });
        }
    }

    function toggleAllCategories(expand) {
        const childRows = $('.child-row');
        const toggleIcons = $('.parent-row .tree-toggle');
        
        if (expand) {
            childRows.removeClass('collapsed');
            toggleIcons.removeClass('fa-chevron-right').addClass('fa-chevron-down');
        } else {
            childRows.addClass('collapsed');
            toggleIcons.removeClass('fa-chevron-down').addClass('fa-chevron-right');
        }
    }
 </script>  
@endpush