@extends('layouts.app-master')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')

@if(auth()->check() && auth()->user()->id == 1)
<div class="container mt-4">
<form method="POST" action="{{ route('settings.update') }}">
    @csrf

    <div class="mb-3">
        <label for="ticket_watchers"> App Maintenance Mode </label>
        <div class="form-check">
            <input type="radio" name="maintenance_mode" value="1" class="form-check-input" id="maintenance_mode_on" {{ isset($setting) && $setting->maintenance_mode ? 'checked' : '' }}>
            <label class="form-check-label" for="maintenance_mode_on">On</label>
        </div>
        <div class="form-check">
            <input type="radio" name="maintenance_mode" value="0" class="form-check-input" id="maintenance_mode_off" {{ isset($setting) && !$setting->maintenance_mode ? 'checked' : '' }}>
            <label class="form-check-label" for="maintenance_mode_off">Off</label>
        </div>
    </div>

    <button class="btn btn-primary">Save Settings</button>
</form>

<a href="{{ route('give-permission-to-storage') }}" class="btn btn-primary mt-2">
    Restart Permission Cron
</a>
@endif

</div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>

    <script>
        $('#filterDom').select2({
            placeholder: 'Select Users',
            maximumSelectionLength: 5,
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('users-list') }}",
                type: "POST",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: "{{ csrf_token() }}",
                        ignoreDesignation: 1,
                        roles: "{{ implode(',', [Helper::$roles['store-phone'], Helper::$roles['store-manager'],Helper::$roles['store-employee'], Helper::$roles['store-cashier'], Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager']]) }}"
                    };
                },
                processResults: function(data, params) {
                    return {
                        results: $.map(data.items, function(item) {
                            return { id: item.id, text: item.text };
                        }),
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                }
            }
        });

        $('#send_mail_at').datetimepicker({
            datepicker: false,
            format: 'H:i',
            step: 15
        });
    </script>
@endpush
