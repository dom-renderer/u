@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')

    <div class="bg-light p-4 rounded">
        <h1> {{ $page_title }} </h1>
        <div class="lead">
            {{ $page_description }}
            @if(auth()->user()->can('users.create'))
                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm float-end">Add User</a>
            @endif

            @if(auth()->user()->can('users.import'))
                <button data-bs-toggle="modal" data-bs-target="#browser-file" class="btn btn-success btn-sm float-end" style="margin-right:10px;">Import User</button>
            @endif

            @if(auth()->user()->can('users.export'))
                <button class="btn btn-success btn-sm float-end" id="export-user" style="margin-right:10px;">Export User</button>
            @endif
        </div>
        
        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-tab-pane" type="button" role="tab" aria-controls="users-tab-pane" aria-selected="true">Users ({{ $userCount }})</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="archived-users-tab" data-bs-toggle="tab" data-bs-target="#archived-users-tab-pane" type="button" role="tab" aria-controls="archived-users-tab-pane" aria-selected="false">Archived ({{ $archivedUserCount }}) </button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                <p id="user-role-table-filter" style="display:none">
                    <select></select>
                </p>
                <div class="table-responsive">
                    <table class="table table-striped fdf" id="users-table" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th scope="col" width="1%">#</th>
                            <th scope="col">First Name</th>
                            <th scope="col">Middle Name</th>
                            <th scope="col">Last Name</th>
                            <th scope="col" width="15%">Username</th>
                            <th scope="col" width="10%">Email</th>
                            <th scope="col" width="10%">Phone Number</th>
                            <th scope="col" width="10%">Role</th>
                            <th scope="col"></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="archived-users-tab-pane" role="tabpanel" aria-labelledby="archived-users-tab" tabindex="0">
                <p id="archived-user-role-table-filter" style="display:none">
                    <select></select>
                </p>
                <div class="table-responsive">
                    <table class="table table-striped" id="archived-users-table" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th scope="col" width="1%">#</th>
                            <th scope="col">First Name</th>
                            <th scope="col">Middle Name</th>
                            <th scope="col">Last Name</th>
                            <th scope="col" width="15%">Username</th>
                            <th scope="col" width="10%">Email</th>
                            <th scope="col" width="10%">Phone Number</th>
                            <th scope="col" width="10%">Role</th>
                            <th scope="col"></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        

    </div> 


<div class="modal fade" id="browser-file" tabindex="-1" aria-labelledby="browser-file" aria-hidden="true">
    <form id="fileUploader" method="POST" action="{{ route('users.import') }}" enctype="multipart/form-data"> @csrf
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Browse File</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="xlsxfile" class="form-label"> Upload a XLSX File </label>
                        <input type="file" name="xlsx" id="xlsxfile" class="form-control" accept=".xlsx" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection


@push('js')
<script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
<script>
    const triggerTabList = document.querySelectorAll('#myTab button');
    const RoleSelectUser = document.querySelector('#user-role-table-filter select');
    const RoleSelectArchivedUser = document.querySelector('#archived-user-role-table-filter select');
    var rolesData = @json($roles);
    
    $(document).ready(function($){

        $(document).on('click', '.deleteGroup', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to archive this User?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, archive it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).parents('form').submit();
                    return true;
                } else {
                    return false;
                }
            })
        });

        $(document).on('click', '.restoreGroup', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to restore this User from archived?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, restore it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).parents('form').submit();
                    return true;
                } else {
                    return false;
                }
            })
        });

        let usersTable = new DataTable('#users-table', {
            dom: '<"d-flex justify-content-between mb-2"<"user-role-table-filter-container">lf>rt<"d-flex flex-column float-start mt-3"pi><"clear">',
            pageLength: 50,
            lengthMenu: [ [10, 25, 50, 100], [10, 25, 50, 100] ],
            ajax: {
                url: "{{route('datatable.users')}}",
                data: function ( d ) {
                    return $.extend( {}, d, {
                        roles: RoleSelectUser.value,
                    });
                }
            },
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                { data: 'DT_RowIndex', searchable: false },
                 { data: 'name' },
                 { data: 'middle_name' },
                 { data: 'last_name' },
                 { data: 'username' },
                 { data: 'email' },
                 { data: 'phone_number' },
                 { data: 'currentrole' },
                 { data: 'action',
                    searchable: false,
                    orderable: false
                 }
            ],
            initComplete: function(settings){
                var api = new $.fn.dataTable.Api( settings );
                $('.user-role-table-filter-container', api.table().container()).append(
                    $('#user-role-table-filter').detach().show()
                );
                
                $(RoleSelectUser).select2({
                    placeholder: "Role Filter",
                    allowClear: true,
                    width:"300px",
                    data: Array.prototype.concat([{id: '', text: 'All', selected: true}], rolesData.map(function(ele) {
                        return {id: ele.name, text:ele.name, selected: false};
                    }))
                }).on('change', function(){
                    usersTable.ajax.reload();
                });
            }
        });
        
        let archivedUsersTable = new DataTable('#archived-users-table', {
            dom: '<"d-flex justify-content-between mb-2"<"archived-user-role-table-filter-container">lf>rt<"d-flex flex-column float-start mt-3"pi><"clear">',
            pageLength: 50,
            lengthMenu: [ [10, 25, 50, 100], [10, 25, 50, 100] ],
            ajax: {
                url: "{{route('datatable.users.archive')}}",
                data: function ( d ) {
                    return $.extend( {}, d, {
                        roles: RoleSelectArchivedUser.value,
                    });
                }
            },
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                { data: 'DT_RowIndex', searchable: false },
                 { data: 'name' },
                 { data: 'middle_name' },
                 { data: 'last_name' },
                 { data: 'username' },
                 { data: 'email' },
                 { data: 'phone_number' },
                 { data: 'currentrole' },
                 { data: 'action',
                    searchable: false,
                    orderable: false
                 }
            ],
            initComplete: function(settings){
                var api = new $.fn.dataTable.Api( settings );
                $('.archived-user-role-table-filter-container', api.table().container()).append(
                    $('#archived-user-role-table-filter').detach().show()
                );
                
                $(RoleSelectArchivedUser).select2({
                    placeholder: "Role Filter",
                    allowClear: true,
                    width:"300px",
                    data: Array.prototype.concat([{id: '', text: 'All', selected: true}], rolesData.map(function(ele) {
                        return {id: ele.name, text:ele.name, selected: false};
                    }))
                }).on('change', function(){
                    archivedUsersTable.ajax.reload();
                });
            }
        });

        triggerTabList.forEach(triggerEl => {
            const tabTrigger = new bootstrap.Tab(triggerEl)

            triggerEl.addEventListener('click', event => {
                event.preventDefault()
                tabTrigger.show()
            })

            triggerEl.addEventListener('shown.bs.tab', event => {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            });
        });


        jQuery.validator.addMethod("extension", function (value, element, param) {
        if (element.files.length > 0) {
            const file = element.files[0];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            return fileExtension === param.toLowerCase();
        }
        return true;
        }, "Please upload a valid file type.");
        jQuery.validator.addMethod("filesize", function (value, element, param) {
        if (element.files.length > 0) {
            return element.files[0].size <= param;
        }
        return true;
        }, "File size must not exceed {0} bytes.");
        $('#fileUploader').validate({
            rules: {
                xlsx: {
                    required: true,
                    extension: "xlsx",
                    filesize: 5242880
                }
            },
            messages: {
                xlsx: {
                    required: "Please select a file",
                    extension: "Please select a XLSX file",
                    filesize: 'File size must not exceed 5MB.'
                }
            },
            submitHandler: function(form, event) { 
                event.preventDefault();
                let formData = new FormData(form);
                $.ajax({
                    url: "{{ route('users.import') }}",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {
                        $('body').find('.LoaderSec').addClass('d-none');
                        if (response.status) {
                            $('#browser-file').modal('hide');
                            $('form#fileUploader')[0].reset();
                            $('.modal-backdrop').remove();
                            usersTable.ajax.reload();
                            Swal.fire('Success', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });


        $('#export-user').on('click', function (e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('users.export') }}",
                type: 'GET',
                cache: false,
                xhrFields:{
                    responseType: 'blob'
                },
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');
                },
                success: function (response) {
                    var url = window.URL || window.webkitURL;
                    var objectUrl = url.createObjectURL(response);
                    var a = $("<a />", {
                        href: objectUrl,
                        download: "users.xlsx"
                    }).appendTo("body")
                    a[0].click()
                    a.remove()
                },
                complete: function () {
                    $('body').find('.LoaderSec').addClass('d-none');
                }
            });            
        });

    });
 </script>  
@endpush