@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-4 rounded">
        <div class="lead">
            
        </div>

        <div class="mx-w-700 mx-auto">
            <div class="card mb-4">
                <div class="card-body">

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Name</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $storetype->name }}
                            </p>
                        </div>
                    </div> <hr>

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Description</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $storetype->description }}
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="{{ route('store-types.index') }}" class="btn btn-default">Back</a>
        </div>
    </div>
@endsection