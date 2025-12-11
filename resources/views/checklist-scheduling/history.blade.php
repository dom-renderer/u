@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')

    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }} </h1>
        <div class="lead">
            {{ $page_description }}
        </div>
        
        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <div class="mb-2">
            <button id="bulk-delete-btn" class="btn btn-danger d-none">Delete Selected</button>
        </div>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-striped" id="role-table" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>Checklist Template</th>
                            <th>Actual File Name</th>
                            <th>Total Records</th>
                            <th>Success Records</th>
                            <th>Failed Records</th>
                            <th>Skipped Records</th>
                            <th>Uploaded By</th>
                            <th>Upload Date</th>
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
        

    </div> 
@endsection


@push('js')
<script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>

<script>
    
    $(document).ready(function($){
        $(document).on('click', '.deleteGroup', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this Department?',
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


        let usersTable = new DataTable('#role-table', {
            pageLength: 50,
            ajax: {
                url: "{{ route('imported-schedulings-history') }}",
                data: function ( d ) {
                    return $.extend( {}, d, {

                    });
                }
            },
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `<input type="checkbox" class="row-checkbox" value="${data}">`;
                    }
                },                
                 { data: 'checklist_name' },
                 { data: 'file_name' },
                 { data: 'total' },
                 { data: 'success' },
                 { data: 'error' },
                 { data: 'skip' },
                 { data: 'uploaded_by' },
                 { data: 'created_at' },
                 { data: 'status' },
                 { data: 'action' }
            ],
            initComplete: function(settings) {

            }
        });

        $(document).on('change', '#select-all', function () {
            $('.row-checkbox').prop('checked', this.checked).trigger('change');
        });

        $(document).on('change', '.row-checkbox', function () {
            const anyChecked = $('.row-checkbox:checked').length > 0;
            $('#bulk-delete-btn').toggleClass('d-none', !anyChecked);

            const allChecked = $('.row-checkbox').length === $('.row-checkbox:checked').length;
            $('#select-all').prop('checked', allChecked);
        });

        $('#bulk-delete-btn').on('click', function () {
            const selectedIds = $('.row-checkbox:checked').map(function () {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) return;

            Swal.fire({
                title: 'Are you sure?',
                text: "Selected records will be deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete them!',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('imported-schedulings-bulk-delete') }}",
                        method: "POST",
                        data: {
                            ids: selectedIds,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (response) {
                            if (response.status) {
                                $('#role-table').DataTable().ajax.reload();
                                $('#bulk-delete-btn').addClass('d-none');
                                Swal.fire('Deleted!', response.message, 'success');                                
                            } else {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            }
                        }
                    });
                }
            });
        });
        
        
    });
 </script>  
@endpush