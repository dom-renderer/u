@extends('layouts.app-master')

@push('css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
@endpush

@section('content')
    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data"> @csrf
        <div class="bg-light p-4 rounded row">
            <div class="col-12">

                <div class="mb-3">
                    <label for="name" class="form-label">First Name</label>
                    <input value="{{ old('name') }}" type="text" class="form-control" name="name" placeholder="Name"
                        required>

                    @if ($errors->has('name'))
                        <span class="text-danger text-left">{{ $errors->first('name') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="middle_name" class="form-label">Middle Name</label>
                    <input value="{{ old('middle_name') }}" type="text" class="form-control" name="middle_name"
                        placeholder="Middle Name">

                    @if ($errors->has('middle_name'))
                        <span class="text-danger text-left">{{ $errors->first('middle_name') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input value="{{ old('last_name') }}" type="text" class="form-control" name="last_name"
                        placeholder="Last Name">

                    @if ($errors->has('last_name'))
                        <span class="text-danger text-left">{{ $errors->first('last_name') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input value="{{ old('email') }}" type="email" class="form-control" name="email"
                        placeholder="Email address" required>
                    @if ($errors->has('email'))
                        <span class="text-danger text-left">{{ $errors->first('email') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="employee_id" class="form-label">Employee ID</label>
                    <input value="{{ old('employee_id') }}" type="text" class="form-control" name="employee_id"
                        placeholder="EMPLOYEE ID">
                    @if ($errors->has('employee_id'))
                        <span class="text-danger text-left">{{ $errors->first('employee_id') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input value="{{ old('username') }}" type="text" class="form-control" name="username"
                        placeholder="Username" required>
                    @if ($errors->has('username'))
                        <span class="text-danger text-left">{{ $errors->first('username') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone number</label>
                    <input value="{{ old('phone_number') }}" type="text" class="form-control" name="phone_number"
                        placeholder="Phone number" required>
                    @if ($errors->has('phone_number'))
                        <span class="text-danger text-left">{{ $errors->first('phone_number') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="profile" class="form-label">Profile image</label>
                    <input value="{{ old('profile') }}" type="file" class="form-control" name="profile">
                    @if ($errors->has('profile'))
                        <span class="text-danger text-left">{{ $errors->first('profile') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Status</label>
                    <select class="form-control" name="status" id="status">
                        <option value="1">Enable</option>
                        <option value="0">Disable</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input value="{{ old('password') }}" type="password" class="form-control" name="password"
                        placeholder="Password" required>
                    @if ($errors->has('password'))
                        <span class="text-danger text-left">{{ $errors->first('password') }}</span>
                    @endif
                </div>

                <!-- Second Part -->
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="">Select role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('role'))
                        <span class="text-danger text-left">{{ $errors->first('role') }}</span>
                    @endif
                </div>

                <div class="mb-3" id="dynamic-role-options">

                </div>

                <div class="mb-3" id="dynamic-dom-options">

                </div>

                <hr>

                <h4> Ticket System Settings </h4>

                <div class="ticket-settings">
                    <select id="ticket_roles" name="ticket_roles[]" multiple>
                        @foreach ($tRoles as $tRole)
                            <option value="{{ $tRole->id }}"> {{ $tRole->name }} </option>
                        @endforeach
                    </select>
                </div>

                <div id="store-view" class="d-none mt-4">
                    <div class="card">
                        <div class="card-header">
                            Select stores for <strong> Divisional Operation Manager </strong>
                        </div>
                        <div class="card-body">
                            <label for=""> Stores </label>
                            <select id="ticket-dom-stores" name="dom_stores[]" multiple>
                                @foreach (\App\Models\Store::whereNull('dom_id')->where('dom_id', '')->get() as $store)
                                    <option value="{{ $store->id }}"> {{ $store->code }} - {{ $store->name }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div id="particular-view" class="d-none mt-4">
                    <div class="card">
                        <div class="card-header">
                            Select issues to assign to <strong> Agent </strong>
                        </div>
                        <div class="card-body">
                            @foreach(\App\Models\Department::whereHas('particulars')->with(['particulars.issues'])->get() as $department)
                                <!-- Issues -->
                                <div class="department mt-4">
                                    <div class="department-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"> {{ $department->name }} </h5>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input deptChckbx" data-iid="chckbx{{ $department->id }}"
                                                type="checkbox" id="selectAll{{ $department->id }}">
                                            <label class="form-check-label" for="selectAll{{ $department->id }}">Select All
                                                {{ $department->name }} </label>
                                        </div>
                                    </div>

                                    @foreach ($department->particulars as $particular)
                                        <div class="section-title mt-2"> {{ $particular->name }} </div>
                                        <div class="switch-group row">
                                            @foreach ($particular->issues as $issue)
                                                <div class="col-4 mt-2">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input chckbx{{ $department->id }}" name="issues[]"
                                                            value="{{ $issue->id }}" type="checkbox" id="issuesChk{{ $issue->id }}">
                                                        <label class="form-check-label" for="issuesChk{{ $issue->id }}">
                                                            {{ $issue->name }} </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach

                                    <hr style="border-color:black;">
                                    <!-- Issues -->
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('users.index') }}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {

            $('#ticket-dom-stores').select2({
                placeholder: "Select stores",
                allowClear: true,
                width: "100%",
                theme: 'classic'
            });

            $('.deptChckbx').on('change', function () {
                let isChecked = $(this).is(':checked');
                let toCheck = $(this).data('iid');

                $(`.${toCheck}`).prop('checked', isChecked);
            });

            $('#ticket_roles').select2({
                placeholder: "Select roles",
                allowClear: true,
                width: "100%",
                theme: 'classic'
            }).on('change', function () {
                let selected = $(this).val();

                if (Array.isArray(selected)) {
                    if (selected.includes('1')) {
                        $('#particular-view').removeClass('d-none');
                    } else {
                        $('#particular-view').addClass('d-none');
                    }

                    if (selected.includes('2')) {
                        $('#store-view').removeClass('d-none');
                    } else {
                        $('#store-view').addClass('d-none');
                    }
                } else {
                    $('#store-view').addClass('d-none');
                    $('#particular-view').addClass('d-none');
                }
            });

            $('#role').select2({
                placeholder: "Select a role",
                allowClear: true,
                width: "100%",
                theme: 'classic'
            }).on('change', function () {
                let selectedRole = $('#role option:selected').val();

                if (!isNaN(selectedRole) && ['10', '6'].includes(selectedRole)) {

                    $('#dynamic-dom-options').html(`
                        <label> Assign DoM </label>
                        <select id="doms" name="doms[]" multiple required>
                        </select>
                    `);

                    if ($('#doms').length > 0) {
                        $('#doms').select2({
                            placeholder: "Select DoM",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('users-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        roles: "{{ implode(',', [Helper::$roles['divisional-operations-manager'], Helper::$roles['operations-manager']  ]) }}",
                                    };
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;

                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            },
                            templateResult: function (data) {
                                if (data.loading) {
                                    return data.text;
                                }

                                var $result = $('<span></span>');
                                $result.text(data.text);
                                return $result;
                            }
                        });
                    }
                } else {
                    $('#dynamic-dom-options').html('');
                }

                if (!isNaN(selectedRole)) {
                    if (['2', '3', '4'].includes(selectedRole)) {
                        $('#dynamic-role-options').html(`
                              <select id="thisStoreDepartmentCOffice" name="office[]" multiple required>
                              </select>
                            `);

                        if ($('#thisStoreDepartmentCOffice').length > 0) {
                            $('#thisStoreDepartmentCOffice').select2({
                                placeholder: "Select a Location",
                                allowClear: true,
                                width: "100%",
                                theme: 'classic',
                                ajax: {
                                    url: "{{ route('stores-list') }}",
                                    type: "POST",
                                    dataType: 'json',
                                    delay: 250,
                                    data: function (params) {
                                        return {
                                            searchQuery: params.term,
                                            page: params.page || 1,
                                            _token: "{{ csrf_token() }}"
                                        };
                                    },
                                    processResults: function (data, params) {
                                        params.page = params.page || 1;

                                        return {
                                            results: $.map(data.items, function (item) {
                                                return {
                                                    id: item.id,
                                                    text: item.text
                                                };
                                            }),
                                            pagination: {
                                                more: data.pagination.more
                                            }
                                        };
                                    },
                                    cache: true
                                },
                                templateResult: function (data) {
                                    if (data.loading) {
                                        return data.text;
                                    }

                                    var $result = $('<span></span>');
                                    $result.text(data.text);
                                    return $result;
                                }
                            });
                        }
                    } else if (['5'].includes(selectedRole)) {
                        $('#dynamic-role-options').html(`
                              <select id="thisStoreDepartmentCOffice" name="office[]" multiple required>
                              </select>
                            `);

                        if ($('#thisStoreDepartmentCOffice').length > 0) {
                            $('#thisStoreDepartmentCOffice').select2({
                                placeholder: "Select a Office",
                                allowClear: true,
                                width: "100%",
                                theme: 'classic',
                                ajax: {
                                    url: "{{ route('corporate-offices-list') }}",
                                    type: "POST",
                                    dataType: 'json',
                                    delay: 250,
                                    data: function (params) {
                                        return {
                                            searchQuery: params.term,
                                            page: params.page || 1,
                                            _token: "{{ csrf_token() }}"
                                        };
                                    },
                                    processResults: function (data, params) {
                                        params.page = params.page || 1;

                                        return {
                                            results: $.map(data.items, function (item) {
                                                return {
                                                    id: item.id,
                                                    text: item.text
                                                };
                                            }),
                                            pagination: {
                                                more: data.pagination.more
                                            }
                                        };
                                    },
                                    cache: true
                                },
                                templateResult: function (data) {
                                    if (data.loading) {
                                        return data.text;
                                    }

                                    var $result = $('<span></span>');
                                    $result.text(data.text);
                                    return $result;
                                }
                            });
                        }
                    } else if (['6', '7', '10'].includes(selectedRole)) {
                        $('#dynamic-role-options').html(`
                              <select id="thisStoreDepartmentCOffice" name="office[]" multiple required>
                              </select>
                            `);

                        if ($('#thisStoreDepartmentCOffice').length > 0) {
                            $('#thisStoreDepartmentCOffice').select2({
                                placeholder: "Select a Department",
                                allowClear: true,
                                width: "100%",
                                theme: 'classic',
                                ajax: {
                                    url: "{{ route('departments-list') }}",
                                    type: "POST",
                                    dataType: 'json',
                                    delay: 250,
                                    data: function (params) {
                                        return {
                                            searchQuery: params.term,
                                            page: params.page || 1,
                                            _token: "{{ csrf_token() }}"
                                        };
                                    },
                                    processResults: function (data, params) {
                                        params.page = params.page || 1;

                                        return {
                                            results: $.map(data.items, function (item) {
                                                return {
                                                    id: item.id,
                                                    text: item.text
                                                };
                                            }),
                                            pagination: {
                                                more: data.pagination.more
                                            }
                                        };
                                    },
                                    cache: true
                                },
                                templateResult: function (data) {
                                    if (data.loading) {
                                        return data.text;
                                    }

                                    var $result = $('<span></span>');
                                    $result.text(data.text);
                                    return $result;
                                }
                            });
                        }
                    } else {
                        $('#dynamic-role-options').html('');
                    }
                }
            });

        });
    </script>
@endpush