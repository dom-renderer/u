@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/orgchart.min.css') }}">
<style>
    #chart-container {
  font-family: Arial;
  height: 620px;
  border: 2px dashed #aaa;
  border-radius: 5px;
  overflow: auto;
  text-align: center;
}

.orgchart {
  background: #fff; 
}
.orgchart td.left, .orgchart td.right, .orgchart td.top {
  border-color: #aaa;
}
.orgchart td>.down {
  background-color: #aaa;
}
.orgchart .middle-level .title {
  background-color: #006699;
}
.orgchart .middle-level .content {
  border-color: #006699;
}
.orgchart .product-dept .title {
  background-color: #009933;
}
.orgchart .product-dept .content {
  border-color: #009933;
}
.orgchart .rd-dept .title {
  background-color: #993366;
}
.orgchart .rd-dept .content {
  border-color: #993366;
}
.orgchart .pipeline1 .title {
  background-color: #996633;
}
.orgchart .pipeline1 .content {
  border-color: #996633;
}
.orgchart .frontend1 .title {
  background-color: #cc0066;
}
.orgchart .frontend1 .content {
  border-color: #cc0066;
}

.orgchart .frontend1 .title {
  background-color: #ffc107;
}

.orgchart .yellow .content {
  border-color: #ffc107;
}

.orgchart .node .title {
    font-size: 12px!important;
    width: max-content!important;
    padding: 3px!important;
    height: max-content!important;
    border-radius: 5px!important;
}

.orgchart .node {
    width: max-content!important;
}

.orgchart .node .content {
  height: max-content!important;
  width: max-content!important;
  padding-right: 10px!important;
}

  div.node {
    display: flex!important;
    align-items: center!important;
    justify-content: center!important;
    flex-direction: column!important;
  }
  .title {
    margin-bottom: 5px;
  }

</style>
@endpush

@section('content')
    <div class="bg-light p-4 rounded">
       
        <div class="mx-w-700 mx-auto mt-4">
            <div class="card mb-4">
                <div class="card-body">
                    <div id="chart-container"></div>
                </div>
            </div>
        </div>

        <a href="{{ route('sections.index') }}" class="btn btn-primary"> Back </a>
    </div>
@endsection

@push('js')
<script src="{{ asset('assets/js/orgchart.min.js') }}"></script>
<script>

var datascource = @json(\App\Models\Section::getDescendantsTree($section->id, true));

    var theData = $('#chart-container').orgchart({
      'data' : datascource,
      'nodeContent': 'title',
      'pan': true,
      'zoom': true,
      'draggable': true,
    });

</script>
@endpush