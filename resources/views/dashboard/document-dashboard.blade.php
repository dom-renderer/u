@extends('layouts.app-master')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
    <style>
        .section {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            background-color: #f8f9fa;
            box-shadow: 0 2px 4px #0000001a;
        }

        .zp_document_dashboard h2 {
            color: #5f0000;
        }

        .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        .select2-container--classic .select2-selection--single {
            height: 40px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__clear {
            height: 37px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 39px !important;
        }

        ul.nav-tabs button.nav-link.active {
            color: #5f0000!important;
            border-bottom: 1px solid #5f0000!important;
        }
    </style>
@endpush

@section('content')
    <div class="row zp_document_dashboard">
        <div class="col-md-6 bg-light p-4 rounded">
            <h2>Near Expiration Document (30 Days)</h2>
            <table id="nearExpirationTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Document</th>
                        <th style="width: 120px;">Document File</th>
                        {{-- <th>Category</th> --}}
                        <th>Location</th>
                        <th style="width: 90px;">Expiry</th>
                        <th style="width: 90px;">Issue</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>

        <div class="col-md-6 bg-light p-4 rounded">
            <h2>Expired Document</h2>
            <table id="expiredTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Document</th>
                        <th style="width: 120px;">Document File</th>
                        {{-- <th>Category</th> --}}
                        <th>Location</th>
                        <th style="width: 90px;">Expiry</th>
                        <th style="width: 90px;">Issue</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            let nearTable = new DataTable('#nearExpirationTable', {
                "dom": '<"d-flex justify-content-between mb-2"<"user-role-table-filter-container">f>rt<"d-flex flex-column float-start mt-3"pi><"clear">',
                ajax: {
                    url: '{{ route("document-dashboard") }}',
                    data: { section: 'near_expiration' }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                searching: false,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'document_name', name: 'document_name' },
                    { data: 'attachment', name: 'document_file' },
                    // { data: 'location_category', name: 'location_category' },
                    { data: 'location', name: 'location' },
                    { data: 'expiry_date', name: 'expiry_date' },
                    { data: 'issue_date', name: 'issue_date' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });
            
            let expiredTable = new DataTable('#expiredTable', {
                "dom": '<"d-flex justify-content-between mb-2"<"user-role-table-filter-container">f>rt<"d-flex flex-column float-start mt-3"pi><"clear">',
                ajax: {
                    url: '{{ route("document-dashboard") }}',
                    data: { section: 'expired' }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                searching: false,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'document_name', name: 'document_name' },
                    { data: 'attachment', name: 'document_file' },
                    // { data: 'location_category', name: 'location_category' },
                    { data: 'location', name: 'location' },
                    { data: 'expiry_date', name: 'expiry_date' },
                    { data: 'issue_date', name: 'issue_date' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // Remind Me Later
            $(document).on('click', '.zp_remindLaterBtn', function() {
                var action_url = $(this).data('url');

                $.ajax({
                    url: action_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: "{{ csrf_token() }}",
                    },
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {
                        $('body').find('.LoaderSec').addClass('d-none');
                        if ( response.status ) {
                            Swal.fire('Success', response.message, 'success');
                            nearTable.ajax.reload();
                            expiredTable.ajax.reload();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            });

            // Delete
            $(document).on('click', '.deleteGroup', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure you want to delete this Document Upload?',
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

        });
    </script>
@endpush