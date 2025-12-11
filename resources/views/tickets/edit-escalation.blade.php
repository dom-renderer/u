@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="bg-light p-4 rounded">
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $page_title }}</h1>
    </div>

    <div class="mt-2">
        @include('layouts.partials.messages')
    </div>

    <form method="POST" action="{{ route('ticket-escalations.update', $escalation->id) }}">
        @csrf
        @method('PUT')

        <div class="card mt-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                        <select id="department_id" name="department_id" required>
                            @if($escalation->department)
                            <option value="{{ $escalation->department->id }}" selected>{{ $escalation->department->name }}</option>
                            @endif
                        </select>
                        @error('department_id')
                        <span class="text-danger text-left">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="particular_id" class="form-label">Particular <span class="text-danger">*</span></label>
                        <select id="particular_id" name="particular_id" required>
                            @if($escalation->particular)
                            <option value="{{ $escalation->particular->id }}" selected>{{ $escalation->particular->name }}</option>
                            @endif
                        </select>
                        @error('particular_id')
                        <span class="text-danger text-left">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="issue_id" class="form-label">Issue <span class="text-danger">*</span></label>
                        <select id="issue_id" name="issue_id" required>
                            @if($escalation->issue)
                            <option value="{{ $escalation->issue->id }}" selected>{{ $escalation->issue->name }}</option>
                            @endif
                        </select>
                        @error('issue_id')
                        <span class="text-danger text-left">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mt-2 mb-2"> Set Escalation to Track Ticket Approval </h3>

        <div class="card mt-3">
            <div class="card-body">
                <h4>Escalation Level 1</h4>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="pending_level1_hours" class="form-label">Time (Hours)</label>
                        <input type="number" min="1" class="form-control" id="pending_level1_hours" name="pending_level1_hours" value="{{ $escalation->pending_level1_hours }}" />
                        @error('pending_level1_hours')
                        <span class="text-danger text-left">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label for="pending_level1_users" class="form-label">Users</label>
                        <select id="pending_level1_users" name="pending_level1_users[]" multiple>
                            @foreach($pendingLevel1Users as $u)
                            <option value="{{ $u->id }}" selected>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        @error('pending_level1_users')
                        <span class="text-danger text-left">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="pending_level1_notifications" class="form-label">Notification Templates</label>
                        <select id="pending_level1_notifications" name="pending_level1_notifications[]" multiple>
                            @foreach($pendingLevel1Templates as $t)
                            <option value="{{ $t->id }}" selected>{{ $t->title }}</option>
                            @endforeach
                        </select>
                        @error('pending_level1_notifications')
                        <span class="text-danger text-left">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h4>Escalation Level 2</h4>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="pending_level2_hours" class="form-label">Time (Hours)</label>
                        <input type="number" min="1" class="form-control" id="pending_level2_hours" name="pending_level2_hours" value="{{ $escalation->pending_level2_hours }}" />
                        @error('pending_level2_hours')
                        <span class="text-danger text-left">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label for="pending_level2_users" class="form-label">Users</label>
                        <select id="pending_level2_users" name="pending_level2_users[]" multiple>
                            @foreach($pendingLevel2Users as $u)
                            <option value="{{ $u->id }}" selected>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        @error('pending_level2_users')
                        <span class="text-danger text-left">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="pending_level2_notifications" class="form-label">Notification Templates</label>
                        <select id="pending_level2_notifications" name="pending_level2_notifications[]" multiple>
                            @foreach($pendingLevel2Templates as $t)
                            <option value="{{ $t->id }}" selected>{{ $t->title }}</option>
                            @endforeach
                        </select>
                        @error('pending_level2_notifications')
                        <span class="text-danger text-left">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mt-2 mb-2"> Set Escalation to Track Ticket Completion </h3>

        <div class="card mt-3">
            <div class="card-body">
                <h4>Escalation Level 1</h4>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="level1_hours" class="form-label">Time (Hours) <span class="text-danger">*</span></label>
                        <input type="number" min="1" class="form-control" id="level1_hours" name="level1_hours" value="{{ $escalation->level1_hours }}" required />
                        @error('level1_hours')
                        <span class="text-danger text-left">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label for="level1_users" class="form-label">Users <span class="text-danger">*</span></label>
                        <select id="level1_users" name="level1_users[]" multiple required>
                            @foreach($level1Users as $u)
                            <option value="{{ $u->id }}" selected>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        @error('level1_users')
                        <span class="text-danger text-left">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="level1_notifications" class="form-label">Notification Templates <span class="text-danger">*</span></label>
                        <select id="level1_notifications" name="level1_notifications[]" multiple required>
                            @foreach($level1Templates as $t)
                            <option value="{{ $t->id }}" selected>{{ $t->title }}</option>
                            @endforeach
                        </select>
                        @error('level1_notifications')
                        <span class="text-danger text-left">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h4>Escalation Level 2</h4>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="level2_hours" class="form-label">Time (Hours) <span class="text-danger">*</span></label>
                        <input type="number" min="1" class="form-control" id="level2_hours" name="level2_hours" value="{{ $escalation->level2_hours }}" required />
                        @error('level2_hours')
                        <span class="text-danger text-left">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label for="level2_users" class="form-label">Users <span class="text-danger">*</span></label>
                        <select id="level2_users" name="level2_users[]" multiple required>
                            @foreach($level2Users as $u)
                            <option value="{{ $u->id }}" selected>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        @error('level2_users')
                        <span class="text-danger text-left">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="level2_notifications" class="form-label">Notification Templates <span class="text-danger">*</span></label>
                        <select id="level2_notifications" name="level2_notifications[]" multiple required>
                            @foreach($level2Templates as $t)
                            <option value="{{ $t->id }}" selected>{{ $t->title }}</option>
                            @endforeach
                        </select>
                        @error('level2_notifications')
                        <span class="text-danger text-left">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
</div>

<div class="mt-3">
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="{{ route('ticket-escalations.index') }}" class="btn btn-default">Back</a>
</div>
</form>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
    const csrfToken = "{{ csrf_token() }}";
    $(document).ready(function() {
        const dept = $('#department_id').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('departments-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: csrfToken
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) {
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
            }
        });

        const particular = $('#particular_id').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('particulars-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        department_id: function() {
                            return $('#department_id').val();
                        },
                        select2: 'particulars',
                        _token: csrfToken
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) {
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
            }
        });

        const issue = $('#issue_id').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('issues-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        particular_id: function() {
                            return $('#particular_id').val();
                        },
                        _token: csrfToken
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) {
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
            }
        });

        dept.on('change', function() {
            particular.val(null).trigger('change');
            issue.val(null).trigger('change');
        });
        particular.on('change', function() {
            issue.val(null).trigger('change');
        });

        $('#level1_users').select2({
            placeholder: 'Select Users',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('users-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: csrfToken,
                        ignoreDesignation: 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) {
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
            }
        });

        $('#level2_users').select2({
            placeholder: 'Select Users',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('users-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: csrfToken,
                        ignoreDesignation: 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) {
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
            }
        });

        $('#level1_notifications').select2({
            placeholder: 'Select Templates',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('notification-template-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: csrfToken
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) {
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
            }
        });

        $('#level2_notifications').select2({
            placeholder: 'Select Templates',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('notification-template-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: csrfToken
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) {
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
            }
        });

        $('#pending_level1_users').select2({
            placeholder: 'Select Users',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('users-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: csrfToken,
                        ignoreDesignation: 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) {
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
            }
        });

        $('#pending_level2_users').select2({
            placeholder: 'Select Users',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('users-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: csrfToken,
                        ignoreDesignation: 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) {
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
            }
        });

        $('#pending_level1_notifications').select2({
            placeholder: 'Select Templates',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('notification-template-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: csrfToken
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) {
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
            }
        });

        $('#pending_level2_notifications').select2({
            placeholder: 'Select Templates',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('notification-template-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: csrfToken
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) {
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
            }
        });
    });
</script>
@endpush