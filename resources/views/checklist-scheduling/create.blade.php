@extends('layouts.app-master')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/clockpicker.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/standalone.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="bg-light p-4 rounded">

        <div class="row mt-4">

            <form method="POST" action="{{ route('checklist-scheduling.store') }}" id="checklistScheduler">
                @csrf

                <div class="card mb-2">
                    <div class="card-body">
                        <h4> Scheduling </h4>

                        <div class="mb-3">
                            <label for="checklist" class="form-label"> Template <span class="text-danger"> * </span> </label>
                            <select name="checklist[]" id="checklist" required multiple>
                            </select>

                            @if ($errors->has('checklist'))
                                <span class="text-danger text-left">{{ $errors->first('checklist') }}</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="loc" class="form-label"> Locations <i id="loctip" class="bi bi-info-circle-fill" data-bs-toggle="tooltip" data-bs-placement="right" title="When you do not select any location. all locations of selected maker employee will be considered."></i> </label>
                            <select name="loc[]" id="loc" multiple>
                            </select>

                            @if ($errors->has('loc'))
                                <span class="text-danger text-left">{{ $errors->first('loc') }}</span>
                            @endif
                        </div>
                      
                        <div class="mb-3">
                            <label for="type">Frequency Type <span class="text-danger"> * </span> </label>
                            <select name="type" id="type" onchange="toggleIntervalAndDays(this.value)" required>
                                <option value="once">Once</option>
                                <option value="every_hour">Every Hour</option>
                                <option value="hourly">Every N Hours</option>
                                <option value="every_day">Daily</option>
                                <option value="daily">Every N Days</option>
                                <option value="weekly">Weekly</option>
                                <option value="biweekly">Biweekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="bimonthly">Bimonthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="semiannual">Semi Annually</option>
                                <option value="annual">Annually</option>
                                <option value="specific_days">Speicific Week Days</option>
                            </select>
                            @if ($errors->has('type'))
                                <span class="text-danger text-left">{{ $errors->first('type') }}</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <div id="interval_field" style="display: none;">
                                <label for="interval">Interval</label>
                                <input type="number" class="form-control" name="interval" id="interval" min="1"
                                    max="23" placeholder="For hours or days">
                            </div>
                        </div>


                        <div class="mb-3">
                            <div id="specific_days_field" style="display: none;">
                                <label for="specific_days">Specific Days</label>
                                <select name="specific_days[]" id="specific_days" multiple>
                                    <option value="monday">Every Monday</option>
                                    <option value="tuesday">Every Tuesday</option>
                                    <option value="wednesday">Every Wednesday</option>
                                    <option value="thursday">Every Thursday</option>
                                    <option value="friday">Every Friday</option>
                                    <option value="saturday">Every Saturday</option>
                                    <option value="sunday">Every Sunday</option>
                                </select>

                                <label for="specific_time">Time:</label>
                                <input type="text" name="specific_time" id="specific_time" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <label for="start_date">Start Date & Time <span class="text-danger"> * </span> </label>
                                <input type="text" name="start_date" id="start_date" class="form-control" required>
                                @if ($errors->has('start_date'))
                                    <span class="text-danger text-left">{{ $errors->first('start_date') }}</span>
                                @endif
                            </div>
                            <div class="col-6" id="end_date_container" style="display: none;">
                                <label for="end_date">End Date & Time </label>
                                <input type="text" name="end_date" id="end_date" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3" id="due_container">
                            <div class="col-6">
                                <label for="start_at">Start At</label>
                                <div class="input-group clockpicker">
                                    <input type="text" name="start_at" id="start_at" class="form-control" value="00:00" required>
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-time"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-6">
                                <label for="completed_by">Completed By</label>
                                <div class="input-group clockpicker">
                                    <input type="text" name="completed_by" id="completed_by" class="form-control" value="22:00" required>
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-time"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-4">
                                <label for="start_at">Grace Start Time</label>
                                <div class="input-group clockpicker">
                                    <input type="text" name="grace_start" id="grace_start" class="form-control" value="00:00" required>
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-time"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-4">
                                <label for="completed_by">Grace End Time</label>
                                <div class="input-group clockpicker">
                                    <input type="text" name="grace_end" id="grace_end" class="form-control" value="00:00" required>
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-time"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-4">
                                <label for="completed_by">Time Required</label>
                                <div class="input-group clockpicker">
                                    <input type="text" name="time_required" id="time_required" class="form-control" value="00:00" required>
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-time"></span>
                                    </span>
                                </div>
                            </div>
                        </div>


                        <div class="mb-3" id="perpetual-container" style="display: none;">
                            <label for="perpetual">
                                <input type="checkbox" id="perpetual" name="perpetual" value="1"
                                    onchange="toggleEndDate(this.checked)">
                                Perpetual
                            </label>
                        </div>

                        <div class="mb-3" id="donotallowlatesubmission-container">
                            <input type="checkbox" id="do_not_allow_late_submission" name="do_not_allow_late_submission" value="1" />
                            <label for="do_not_allow_late_submission"> Do not allow late submission </label>
                        </div>

                    </div>
                </div>

                <div class="card mb-2">
                    <div class="card-body">
                        <h4> Maker </h4>

                        <div class="mb-3">
                            <label for="maker_role" class="form-label"> Role <span class="text-danger"> * </span> </label>
                            <select name="maker_role[]" id="maker_role" multiple required>
                                @foreach ($makerRoles as $makerRole)
                                    <option value="{{ $makerRole->id }}"> {{ $makerRole->name }} </option>
                                @endforeach
                            </select>

                            @if ($errors->has('maker_role'))
                                <span class="text-danger text-left">{{ $errors->first('maker_role') }}</span>
                            @endif
                        </div>
                        
                        <div class="mb-3">
                            <label for="maker_employee" class="form-label"> Employee </label>
                            <select name="maker_employee[]" id="maker_employee" multiple>
                            </select>

                            @if ($errors->has('maker_employee'))
                                <span class="text-danger text-left">{{ $errors->first('maker_employee') }}</span>
                            @endif
                        </div>   

                        <div class="mb-3">

                            <div class="form-group">
                                <input type="radio" name="assination_type" id="type1" value="1" checked>
                                <label for="type1" class="form-label"> Assign to all employees of selected roles </label>
                            </div>
                            
                            <div class="form-group">
                                <input type="radio" name="assination_type" id="type2" value="2">
                                <label for="type2" class="form-label"> Assign to only selected employees of selected roles </label>
                            </div>
    
                            <div class="form-group">
                                <input type="radio" name="assination_type" id="type3" value="3">
                                <label for="type3" class="form-label"> Assign to only employees except selected users of selected roles </label>
                            </div>

                        </div>   

                    </div>
                </div>

                <div class="card mb-2">
                    <div class="card-body">
                        <h4> Checker </h4>

                        <div class="mb-3">
                            <label for="checker_role" class="form-label"> Role <span class="text-danger"> * </span> </label>
                            <select name="checker_role" id="checker_role" required>
                                <option value="" selected></option>
                                @foreach ($checkerRoles as $checkerRole)
                                    <option value="{{ $checkerRole->id }}"> {{ $checkerRole->name }} </option>
                                @endforeach
                            </select>

                            @if ($errors->has('checker_role'))
                                <span class="text-danger text-left">{{ $errors->first('checker_role') }}</span>
                            @endif
                        </div>
                        
                        <div class="mb-3">
                            <label for="checker_employee" class="form-label"> Employee <span class="text-danger"> * </span> </label>
                            <select name="checker_employee" id="checker_employee" required>
                                <option value="" selected></option>
                            </select>

                            @if ($errors->has('checker_employee'))
                                <span class="text-danger text-left">{{ $errors->first('checker_employee') }}</span>
                            @endif
                        </div>   

                    </div>
                </div>

        </div>

        <button type="submit" class="btn btn-primary actualSubmitButton">Save</button>
        <a href="{{ route('checklist-scheduling.index') }}" class="btn btn-default">Back</a>
        </form>
    </div>

    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/clockpicker.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            $('.clockpicker').clockpicker({
                placement: 'top',
                align: 'left',
                donetext: 'Done'
            });

            $('#loctip').tooltip()

            $('#maker_role').select2({
                placeholder: 'Select Role',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            }).on('change', function () {
                $('#maker_employee').val(null).trigger('change');
            });

            $('#checker_role').select2({
                placeholder: 'Select Role',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            }).on('change', function () {
                $('#checker_employee').val(null).trigger('change');
            });

            $('#checker_employee').select2({
                placeholder: 'Select Employee',
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
                            roles: function () {
                                return $('#checker_role option:selected').val();
                            }
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
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            });
           
            $('#maker_employee').select2({
                placeholder: 'Select Employee',
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
                            roles: function () {
                                return $('#maker_role').val();
                            }
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
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            });

            $('#loc').select2({
                placeholder: 'Select location',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('stores-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,  
                            _token: "{{ csrf_token() }}",
                            showCode: 1
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
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function () {

            });

            function parseTime(str) {
                const [hours, minutes] = str.split(':').map(Number);
                return hours * 60 + minutes;
            }

            $('#checklistScheduler').validate({
                rules: {
                    'checklist[]': {
                        required: true
                    },
                    type: {
                        required: true
                    },
                    interval: {
                        required: function(element) {
                            return $('#type').val() === 'hourly' || $('#type').val() === 'daily';
                        }
                    },
                    'specific_days[]': {
                        required: function(element) {
                            return $('#type').val() === 'specific_days';
                        }
                    },
                    start_date: {
                        required: true
                    },
                    end_date: {
                        required: function(element) {
                            return !$('#perpetual').is(':checked');
                        }
                    }
                },
                messages: {
                    'checklist[]': {
                        required: "Please select a checklist"
                    },
                    type: {
                        required: "Please select a frequency type"
                    },
                    interval: {
                        required: "Please enter an interval"
                    },
                    'specific_days[]': {
                        required: "Please select specific days"
                    },
                    start_date: {
                        required: "Please select a start date"
                    },
                    end_date: {
                        required: "Please select an end date"
                    }
                },
                errorPlacement: function(error, element) {
                    error.appendTo(element.parent("div"));
                }
            });

            $('#perpetual').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#end_date').val('');
                }
            });

            $('#type').select2({
                placeholder: 'Select Frequency',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            }).on('change', function() {
                if (!($('#type option:selected').val() == 'hourly' || $('#type option:selected').val() ==
                        'daily')) {
                    $('#interval').val('');
                } else if ($('#type option:selected').val() != 'specific_days') {
                    $('#specific_days').val(null).trigger('change');
                    $('#specific_time').val('');
                }
            });

            $('#checklist').select2({
                placeholder: 'Select Checklist Template',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('checklists-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            type: 1
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
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            });

            $('#specific_days').select2({
                placeholder: 'Select Frequency',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            $('#start_date').datetimepicker({
                format: 'd-m-Y H:i'
            });

            $('#end_date').datetimepicker({
                format: 'd-m-Y H:i'
            });

            $('#specific_time').datetimepicker({
                datepicker: false,
                format: 'H:i'
            });

        });

        function toggleEndDate(isChecked) {
            const endDateField = document.getElementById('end_date_container');
            endDateField.style.display = isChecked ? 'none' : 'block';
            if (isChecked) {
                document.getElementById('end_date').value = '';
            }
        }

        function toggleIntervalAndDays(type) {
            document.getElementById('interval_field').style.display = type === 'hourly' || type === 'daily' ? 'block' :
                'none';
            document.getElementById('specific_days_field').style.display = type === 'specific_days' ? 'block' : 'none';

            const endDateField = document.getElementById('end_date_container');
            endDateField.style.display = type == 'once' ? 'none' : 'block';

            const thisEle = document.getElementById('perpetual-container');
            thisEle.style.display = type == 'once' ? 'none' : 'block';
        }
    </script>
@endpush
