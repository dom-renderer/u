<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">

<div class="card mb-2">
  <div class="card-body">

      <div class="row">
          <div class="col-2 col-xl-4 col-lg-4 col-xxl-2">
              <label class="col-form-label" for="task-filter"> Task </label>
              <select id="task-filter">

              </select>
          </div>
          <div class="col-2 col-xl-4 col-lg-4 col-xxl-2">
              <label class="col-form-label" for="status-filter"> Status </label>
              <select id="status-filter">
                <option value=""></option>
              @foreach (\App\Models\Status::get() as $status)
                <option value="{{ $status->id }}"> {{ $status->name }} </option>
              @endforeach
              </select>
          </div>
          <div class="col-2 col-xl-4 col-lg-4 col-xxl-2">
              <label class="col-form-label" for="priority-filter"> Priority </label>
              <select id="priority-filter">
              <option value=""></option>
              @foreach (\App\Models\Priority::get() as $priority)
                <option value="{{ $priority->id }}"> {{ $priority->name }} </option>
              @endforeach
              </select>
          </div>

          <div class="col-2 col-xl-4 col-lg-4 col-xxl-2">
              <label class="col-form-label" for="department-filter"> Department </label>
              <select id="department-filter">
              <option value=""></option>                
              @foreach (\App\Models\Department::get() as $department)
                <option value="{{ $department->id }}"> {{ $department->name }} </option>
              @endforeach
              </select>
          </div>

          <div class="col-2 col-xl-4 col-lg-4 col-xxl-2">
              <label class="col-form-label" for="createdby-filter"> Created By </label>
              <select id="createdby-filter">
              <option value=""></option>                
              </select>
          </div>

          <div class="col-2 col-xl-2 col-lg-4">
                <button id="filter-data" class="btn btn-secondary" style="position: relative;top:34px;"> Search </button>
                <button id="filter-data-clear" class="btn btn-danger d-none" style="position: relative;top:34px;"> Clear </button>
            </div>

      </div>

  </div>
</div>

<table class="ticketit-table table table-striped  dt-responsive nowrap" style="width:100%">
    <thead>
        <tr>
            <td>#</td>
            <td>Ticket Id</td>
            <td>Task</td>
            <td>Subject</td>
            <td>Status</td>
            <td>Priority</td>
            <td>Department</td>
            <td>Last updated</td>
            <td>Escalation</td>
          @if( $u->isAgent() || $u->isAdmin() )
            <td>Created By</td>
          @endif
          <td>Action</td>
        </tr>
    </thead>
</table>
