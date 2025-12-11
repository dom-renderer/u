@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/custom-select-style.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="bg-light p-4 rounded">
      

        <div class="container mt-4">
            <form method="POST" id="updateCatForm" action="{{ route('sections.update', $id) }}"> @csrf @method('PUT')
                <input type="hidden" name="id" value="{{ $id }}">
                <div class="mb-3">
                    <label for="parent" class="form-label">Parent section</label>
                    <select name="parent" id="parent">
                        @if(!empty($category->parent_id) && $category->parent_id != '0')
                            <option value="{{ $category->parent_id }}" selected> {{ $category->parent->name ?? '-' }} </option>
                        @endif
                    </select>
                    @if ($errors->has('parent'))
                        <span class="text-danger text-left">{{ $errors->first('parent') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger"> * </span> </label>
                    <input value="{{ old('name', $category->name) }}" type="text" class="form-control" name="name"
                        placeholder="Name" required>
                    @if ($errors->has('name'))
                        <span class="text-danger text-left">{{ $errors->first('name') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="checklist" class="form-label"> Checklist </label>
                    <select name="checklist[]" id="checklist" multiple>
                        @foreach($category->checklists as $checklist)
                            <option value="{{ $checklist->checklist->id }}" selected> {{ $checklist->checklist->name }} </option>
                        @endforeach
                    </select>
                </div>


                <button type="submit" class="btn btn-primary" id="sbmt-btn">Save</button>
                <a href="{{ route('sections.index') }}" class="btn btn-default">Back</a>
            </form>
        </div>

    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {

            $('#parent').select2({
                allowClear: true,
                placeholder: "Select a parent section",
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('sections-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,  
                            _token: "{{ csrf_token() }}",
                            except: {{ $category->id }}
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

            $('#checklist').select2({
            allowClear: true,
            placeholder: "Select checklists",
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
                        onlyactive: 1
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
            
            $('#sbmt-btn').on('click', function (e) {
                e.preventDefault();

                let theForm = $('#updateCatForm');

                $.ajax({
                    url: "{{ route('get-sub-sec-count') }}",
                    type: 'POST',
                    headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"},
                    data: {
                        'id' : "{{ $category->id }}",
                        'parent' : function () {
                            return $('#parent option:selected').val();
                        }
                    },
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        if (response.count > 0) {
                            Swal.fire({
                                title: `This section has ${response.count} sub-section. Are you sure you want update the parent section?`,
                                text: "You won't be able to revert this!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Yes, update it!'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    theForm.submit();
                                    return true;
                                } else {
                                    return false;
                                }
                            })
                        } else {
                            theForm.submit();
                        }
                    },
                    complete: function (response) {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });

            });
        });
    </script>
@endpush
