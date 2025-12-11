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
                            <p class="mb-0">First Name</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $user->name }}
                            </p>
                        </div>
                    </div><hr>

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Middle Name</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $user->middle_name }}
                            </p>
                        </div>
                    </div><hr>

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Last Name</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $user->last_name }}
                            </p>
                        </div>
                    </div><hr>

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Username</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $user->username }}
                            </p>
                        </div>
                    </div><hr>

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Employee ID</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $user->employee_id }}
                            </p>
                        </div>
                    </div><hr>

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Phone number</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $user->phone_number }}
                            </p>
                        </div>
                    </div><hr>

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Email</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $user->email }}
                            </p>
                        </div>
                    </div><hr>

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Role</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $user->roles->first()->name ?? '-' }}
                            </p>
                        </div>
                    </div><hr>


                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Status</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $user->status == 1 ? 'Enabled' : 'Disabled' }}
                            </p>
                        </div>
                    </div> <hr>


                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Profile</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                @if(!empty($user->profile) && file_exists(storage_path("app/public/users/{$user->profile}")))
                                <img src="{{ asset("storage/users/{$user->profile}") }}" style="height:100px;width:100px;border:1px solid black;">
                                @else
                                    No profile picture uploaded
                                @endif
                            </p>
                        </div>
                    </div>

                    @if(!empty($store))

                    <hr>

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0"> {{ $type }} </p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                <ul>
                                    @forelse ($store as $item)
                                        <li> {{ $item }} </li>
                                    @empty
                                    -
                                    @endforelse
                                </ul>

                            </p>
                        </div>
                    </div>

                    @endif

                </div>
            </div>
        </div>
        
        <div class="mt-4">
            @php
            use App\Models\User;
            $user = User::withTrashed()->find($user->id);
             
            @endphp
            @if(!$user->trashed())
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-info">Edit</a>
            @endif
            <a href="{{ route('users.index') }}" class="btn btn-default">Back</a>
        </div>
    </div>
@endsection