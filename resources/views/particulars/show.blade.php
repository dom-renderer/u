@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }}</h1>
        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <div class="mt-4">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <div>{{ $particular->name }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <div>{{ optional(\App\Models\Department::withTrashed()->find($particular->department_id))->name ?? '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div>{{ $particular->status ? 'Active' : 'InActive' }}</div>
                    </div>
                </div>
            </div>

            <a href="{{ route('particulars.index') }}" class="btn btn-default mt-3">Back</a>
        </div>
    </div>
@endsection

@push('js')
<script type="text/javascript">

</script>
@endpush