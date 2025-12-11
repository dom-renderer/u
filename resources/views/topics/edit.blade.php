@extends('layouts.app-master')

@push('css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="bg-light p-4 rounded">
      

        <div class="container mt-4">
            <form method="POST" id="updateCatForm" action="{{ route('topics.update', $id) }}"> @csrf @method('PUT')
                <input type="hidden" name="id" value="{{ $id }}">
                <div class="mb-3">
                    <label for="parent" class="form-label">Parent topic</label>
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
                    <label for="name" class="form-label">Name</label>
                    <input value="{{ old('name', $category->name) }}" type="text" class="form-control" name="name"
                        placeholder="Name" required>
                    @if ($errors->has('name'))
                        <span class="text-danger text-left">{{ $errors->first('name') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Status</label>
                    <select name="status" class="form-control" id="status">
                        <option value="1" @if($category->status) selected @endif> Enable </option>
                        <option value="0" @if(!$category->status) selected @endif> Disable </option>
                    </select>
                    @if ($errors->has('status'))
                        <span class="text-danger text-left">{{ $errors->first('status') }}</span>
                    @endif
                </div>


                <button type="submit" class="btn btn-primary" id="sbmt-btn">Save</button>
                <a href="{{ route('topics.index') }}" class="btn btn-default">Back</a>
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
                placeholder: "Select a parent topic",
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('topics-select2') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,  
                            _token: "{{ csrf_token() }}",
                            except: "{{ implode(',', [$category->id]) }}"
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
                    url: "{{ route('get-sub-cat-count') }}",
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
                                title: `This topic has ${response.count} sub-topics. Are you sure you want update the parent topic?`,
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
