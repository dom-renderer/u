@extends('layouts.app-master')

@push('css')

@endpush

@section('content')
    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            @foreach($form->schema as $key => $value)
                <button class="nav-link @if($loop->first) active @endif" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home-{{ $key }}" type="button" role="tab" aria-controls="nav-home" aria-selected="true"> Page {{ $loop->iteration }} </button>
            @endforeach
        </div>
    </nav>

    <div class="tab-content" id="nav-tabContent">
        @foreach($form->schema as $key => $value)
            <div class="tab-pane fade show @if($loop->first) active @endif" id="nav-home-{{ $key }}" role="tabpanel" aria-labelledby="nav-home-tab">
                <div class="render-wrap-{{ $key }}"></div>
            </div>
        @endforeach
    </div>
@endsection


@push('js')
<script src="{{ url('assets/form-builder/form-render.min.js') }}"></script>
<script type="text/javascript">


@foreach($form->schema as $key => $value)
const wrap{{$key}} = $('.render-wrap-{{ $key }}');
const formRender{{$key}} = wrap{{$key}}.formRender();
wrap{{$key}}.formRender('render', @json($form->schema)[{{ $key }}]);
@endforeach
</script>
@endpush