@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }}</h1>
        <div class="lead">
            {{ $page_description }}
            @if (auth()->user()->can('particulars.create'))
                <a href="{{ route('particulars.create') }}" class="btn btn-primary btn-sm float-end">Add Particular</a>
            @endif
        </div>

        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" role="tabpanel" tabindex="0">
                <table class="table table-striped" id="particulars-table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Department</th>
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
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).on('click', '.deleteGroup', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this Particular?',
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

        let table = new DataTable('#particulars-table', {
            ajax: {
                url: "{{ route('particulars.index') }}",
                data: function ( d ) {
                    return $.extend( {}, d, {});
                }
            },
            "aLengthMenu": [[50, 100, -1], [50, 100, 'All']],            
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                 { data: 'name' },
                 { data: 'department', searchable: false },
                 { data: 'status' },
                 { data: 'action', searchable: false }
            ]
        });
    });
</script>
@endpush