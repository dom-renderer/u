@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<style>
#sortable tr.sortable-enabled {
    cursor: move;
    background-color: #f9f9f9;
}
</style>
@endpush

@section('content')

    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }} </h1>
        <div class="lead">
            {{ $page_description }}
            @if (auth()->user()->can('checklists.create'))
                <a href="{{ route('checklists.create') }}" class="btn btn-primary btn-sm float-end me-2">Add Checklists Template</a>
            @endif
            @if(auth()->user()->can('multi-checklist-import'))
                <a href="{{ route('multi-checklist-import') }}" class="btn btn-success btn-sm float-end me-2">Multi - Checklist Import</a>
                <button id="openAccordionBtn" class="btn btn-info btn-sm float-end me-2">Store Multi - Checklist Import</button>
            @endif
            <button id="toggleSort" class="btn btn-secondary btn-sm float-end me-2">Enable Sort</button>
        </div>
        
        <div class="accordion mt-2" id="myAccordion">
            <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                    Store Multi - Checklist Import
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                data-bs-parent="#myAccordion">
                <div class="accordion-body">

                <a href="{{ url('store-multi-checklist.xlsx') }}" download class="btn btn-success btn-sm float-end mb-2"> Download Store - Multi Checklist Sample File </a>

                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Frequency</th>
                            <th>Code</th>
                            <th>Additional Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Once</td>
                            <td>once</td>
                            <td>Occurs once</td>
                        </tr>
                        <tr>
                            <td>Every Hour</td>
                            <td>every_hour</td>
                            <td>Occurs every hour</td>
                        </tr>
                        <tr>
                            <td>Every N Hour</td>
                            <td>every_n_hour</td>
                            <td>Occurs every N hours. Example: Every 3 hours or every 5 hours.</td>
                        </tr>
                        <tr>
                            <td>Daily</td>
                            <td>daily</td>
                            <td>Occurs once every 24 hours at the specified time</td>
                        </tr>
                        <tr>
                            <td>Every N Day</td>
                            <td>every_n_day</td>
                            <td>Occurs every N days. Example: Every 3 days or every 5 days.</td>
                        </tr>
                        <tr>
                            <td>Weekly</td>
                            <td>weekly</td>
                            <td>Occurs every week</td>
                        </tr>
                        <tr>
                            <td>Biweekly</td>
                            <td>biweekly</td>
                            <td>Occurs every 2 weeks</td>
                        </tr>
                        <tr>
                            <td>Monthly</td>
                            <td>monthly</td>
                            <td>Occurs once a month</td>
                        </tr>
                        <tr>
                            <td>Bimonthly</td>
                            <td>bimonthly</td>
                            <td>Occurs every 2 months</td>
                        </tr>
                        <tr>
                            <td>Quarterly</td>
                            <td>quarterly</td>
                            <td>Occurs once every 3 months</td>
                        </tr>
                        <tr>
                            <td>Semi Annually</td>
                            <td>semi_annually</td>
                            <td>Occurs twice a year</td>
                        </tr>
                        <tr>
                            <td>Annually</td>
                            <td>annually</td>
                            <td>Occurs once every year</td>
                        </tr>
                        <tr>
                            <td>Specific Week Days</td>
                            <td>specific_week_days</td>
                            <td>Occurs on specific weekdays (e.g., monday;wednesday;friday), Also can add interval like every 2 hour on monday;wednesday;friday</td>
                        </tr>
                    </tbody>
                </table>


                    <form action="{{ route('store-multi-checklist-import') }}" method="POST" enctype="multipart/form-data" id="checklistImport"> 
                        @csrf
                        <div class="mb-3">
                            <label for="import" class="form-label"> Browse File (XLSX) <span class="text-danger"> * </span> </label>
                            <input type="file" name="import" id="import" class="form-control" accept=".xlsx" required>

                            @if ($errors->has('import'))
                                <span class="text-danger text-left">{{ $errors->first('import') }}</span>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-success actualSubmitButton">Import</button>
                    </form>

                </div>
            </div>
            </div>
        </div>

        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                <table class="table table-striped" id="role-table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody id="sortable">
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection

@push('js')
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/sortable.min.js') }}"></script>
<script>

    let sortable = null;
    let sortingEnabled = false;
    
    $(document).ready(function($){

        var collapseElement = document.getElementById('collapseOne');

        document.getElementById('openAccordionBtn').addEventListener('click', function () {
            var collapseElement = document.getElementById('collapseOne');
            var bsCollapse = new bootstrap.Collapse(collapseElement, {
                toggle: true
            });
        });

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
                    url: "{{ route('store-multi-checklist-import') }}",
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

        function initSortable() {
            if (sortable) {
                sortable.destroy();
            }

            sortable = new Sortable(document.getElementById('sortable'), {
                animation: 150,
                disabled: !sortingEnabled,
                onEnd: function (evt) {
                    let order = [];
                    $('#sortable tr').each(function(index, element) {
                        let id = $(element).attr('id').replace('row_', '');
                        order.push(id);
                    });

                    $.ajax({
                        url: "{{ route('checklists.reorder') }}",
                        method: 'POST',
                        data: {
                            order: order,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Order updated successfully',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        },
                        error: function () {
                            Swal.fire('Error', 'Could not update order', 'error');
                        }
                    });
                }
            });
        }        

        $(document).on('click', '.deleteGroup', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this Checklist?',
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

        $('#role-table').on('draw.dt', function () {
            initSortable();
        });

        $('#toggleSort').on('click', function() {
            sortingEnabled = !sortingEnabled;

            $(this).text(sortingEnabled ? 'Disable Sort' : 'Enable Sort');

            if (sortable) {
                sortable.option('disabled', !sortingEnabled);
            }

            Swal.fire({
                icon: 'info',
                title: sortingEnabled ? 'Sorting Enabled' : 'Sorting Disabled',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500
            });
        });

        let usersTable = new DataTable('#role-table', {
            pageLength: 50,
            "aLengthMenu": [[10, 50, 100, 250, -1], [10, 50, 100, 250, 'All']],
            ajax: {
                url: "{{ route('checklists.index') }}",
                data: function ( d ) {
                    return $.extend( {}, d, {

                    });
                }
            },
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                 { data: 'name' },
                 { data: 'action' }
            ],
            initComplete: function(settings) {

            }
        });
        
    });
 </script>  
@endpush