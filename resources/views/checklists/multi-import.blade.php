@extends('layouts.app-master')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="bg-light p-4 rounded">

        <form method="GET" action="" id="checklistImport" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label for="import" class="form-label"> Browse File (XLSX) <span class="text-danger"> * </span> </label>
                <input type="file" name="import" id="import" class="form-control" accept=".xlsx" required>

                @if ($errors->has('import'))
                    <span class="text-danger text-left">{{ $errors->first('import') }}</span>
                @endif
            </div>

            <button type="submit" class="btn btn-success actualSubmitButton">Import</button>
            <a href="{{ route('checklist-scheduling.index') }}" class="btn btn-default">Back</a>

        </form>

    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script type="text/javascript">

        $(document).ready(function () {

            $('#checklistImport').validate({
                rules: {
                    import: {
                        required: true
                    }
                },
                messages: {
                    import: {
                        required: "Upload a XLSX file"
                    }
                },
                submitHandler: function (form, event) {
                    event.preventDefault();

                    let formData = new FormData(form)

                    $.ajax({
                        url: "{{ url()->current() }}",
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            $('body').find('.LoaderSec').removeClass('d-none');                            
                        },
                        success: function (response) {
                            if (response.status) {
                                Swal.fire('Success', 'Imported successfully', 'success');
                                window.location = "{{ route('scheduled-tasks.index') }}";
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;
                                let errorMessages = '';

                                for (let key in errors) {
                                    if (errors.hasOwnProperty(key)) {
                                        errors[key].forEach(function(message) {
                                            errorMessages += `• ${message}<br>`;
                                        });
                                    }
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validation Error',
                                    html: errorMessages
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Something went wrong. Please try again.'
                                });
                            }
                        },
                        complete: function (response) {
                            $('body').find('.LoaderSec').addClass('d-none');
                        }
                    });
                }
            });

            $('#checklist').select2({
                placeholder: 'Select Checklist',
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
           
            $('#clist2').select2({
                placeholder: 'Select Checklist',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            

        });


    </script>
@endpush
