@extends('ticket.layouts.master')

@section('title', "Helpdesk main page"." - ".Helper::setting()->name)
@section('page_title', 'Tickets')


@section('ticketit_header')
    {!! link_to_route($setting->grab('main_route').'.create', "Create New Ticket", null, ['class' => 'btn btn-primary']) !!}
@stop

@section('ticketit_content_parent_class', 'pl-0 pr-0')

@section('ticketit_content')
    <div id="message"></div>
    @include('ticket.tickets.partials.datatable')
@stop

@section('script')
	<script src="https://cdn.datatables.net/v/bs4/dt-{{ \App\Helpers\Cdn::DataTables }}/r-{{ \App\Helpers\Cdn::DataTablesResponsive }}/datatables.min.js"></script>
	<script src="{{ asset('assets/js/select2.min.js') }}"></script>
	<script>

	    let usersTable = $('.ticketit-table').DataTable({
	        processing: false,
	        serverSide: true,
	        responsive: true,
            pageLength: {{ $setting->grab('paginate_items') }},
        	lengthMenu: {{ json_encode($setting->grab('length_menu')) }},
	        ajax: {
                url: '{!! route($setting->grab('main_route').'.data', $complete) !!}',
                data: {
                    task: function () {
                        return $('#task-filter option:selected').val();
                    },
                    status: function () {
                        return $('#status-filter option:selected').val();
                    },
                    priority: function () {
                        return $('#priority-filter option:selected').val();
                    },
                    department: function () {
                        return $('#department-filter option:selected').val();
                    },                                                            
                    createdby: function () {
                        return $('#createdby-filter option:selected').val();
                    }
                }
            },
            order: [ [1, 'desc'] ],
	        columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
	            { data: 'ticket_number', name: 'ticketit.ticket_number' },
	            { data: 'code', name: 'checklist_tasks.code' },
	            { data: 'subject', name: 'subject' },
	            { data: 'status', name: 'ticketit_statuses.name' },
	            { data: 'p_name', name: 'ticketit_priorities.name' },
	            { data: 'd_name', name: 'departments.name' },
	            { data: 'updated_at', name: 'ticketit.updated_at' },
	            { data: 'escalation_status', orderable: false, searchable: false },
	            @if( $u->isAgent() || $u->isAdmin() )
	            	{ data: 'owner', name: 'users.name' },
	            @endif
				{ data: 'manageticket', orderable: false, searchable: false },
	        ]
	    });

        $(document).on('click', '#filter-data', function () {
            usersTable.ajax.reload();

            let taskFilter = $('#task-filter').val();
            let statusFilter = $('#status-filter').val();
            let priorityFilter = $('#priority-filter').val();
			let departmentFilter = $('#department-filter').val();
			let createdByFilter = $('#createdby-filter').val();

            if (anyIsset(taskFilter, statusFilter, priorityFilter, departmentFilter, createdByFilter)) {
                $('#filter-data-clear').removeClass('d-none');
            } else {
                $('#filter-data-clear').addClass('d-none');
            }
        });

        $(document).on('click', '#filter-data-clear', function () {
            if (!$('#filter-data-clear').hasClass('d-none')) {
                $('#filter-data-clear').addClass('d-none');
            }

            $('#task-filter').val(null).trigger('change');
            $('#status-filter').val(null).trigger('change');
            $('#priority-filter').val(null).trigger('change');
			$('#department-filter').val(null).trigger('change');
			$('#createdby-filter').val(null).trigger('change');

            usersTable.ajax.reload();
        });

		$('#createdby-filter').select2({
            placeholder: "Select a User",
            allowClear: true,
            width: "100%",
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
                        ignoreDesignation: 1
                    }
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

		$('#task-filter').select2({
            placeholder: "Select a Task",
            allowClear: true,
            width: "100%",
            theme: 'classic',
            ajax: {
                url: "{{ route('scheduled-task-list') }}",
                type: "POST",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,  
                        _token: "{{ csrf_token() }}",
                        ignoreDesignation: 1
                    }
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

		$('#status-filter').select2({
            placeholder: "Select a Status",
            allowClear: true,
            width: "100%",
            theme: 'classic'
        });

		$('#priority-filter').select2({
            placeholder: "Select a Priority",
            allowClear: true,
            width: "100%",
            theme: 'classic'
        });

		$('#department-filter').select2({
            placeholder: "Select a Department",
            allowClear: true,
            width: "100%",
            theme: 'classic'
        });
		
	</script>
@append
