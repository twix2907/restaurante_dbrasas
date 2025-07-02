@extends('layouts.admin')

@section('content')
    <div class="row col-md-8 offset-md-2">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h3 class="m-0 fw-bold text-primary"> Edit User</h3>
                <a href="" class="btn btn-primary">Volver</a>
            </div>
            <div class="card-body">
                <x-errors />
                <form method="POST" action="{{ route('users.update',$user->id) }}" class="form-row" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="form-group col-md-6">
                        <label for="name">Name*</label>
                        <input class="form-control" id="name" type="name" value="{{ $user->name }}" name="name">
                    </div>

                    <div class="form-group col-md-6">
                        <label for="last_name">Last name</label>
                        <input class="form-control" id="last_name" type="text" value="{{ $user->last_name }}" name="last_name">
                    </div>

                    <div class="form-group col-md-6">
                        <label for="email">Email*</label>
                        <input class="form-control" id="email" type="email" value="{{ $user->email }}" name="email">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="address">Address</label>
                        <input class="form-control" id="address" type="text" value="{{ $user->address }}" name="address">
                    </div>

                    <div class="form-group col-md-6">
                        <label for="password">Password*</label>
                        <input class="form-control" id="password" type="password" name="password">
                    </div>

                    <div class="form-group col-md-6">
                        <label for="password_confirmation">Confirm Password*</label>
                        <input class="form-control" id="password_confirmation" type="password" name="password_confirmation">
                    </div>
                    <div class="form-group col-md-6">

                        <label for="phone">Phone</label>
                        <input class="form-control" id="phone" type="text" value="{{ $user->phone }}" name="phone">

                    </div>
                    <div class="form-group col-md-6">
                        <div class="form-check" style="margin-top: 2rem">
                            <input class="form-check-input" type="checkbox" id="admin" name="admin" @if($user->admin) checked @endif>
                            <label class="form-check-label" for="admin">
                              Admin
                            </label>
                        </div>
                    </div>

                    <div class="col-6 text-center">
                        <img src="{{asset($user->image)}}" width="100">
                    </div>

                    <div class="col-6">
                        <div class="form-wrap">
                            <label for="image">Imagen</label>
                            <input type="file" class="form-control-file" name="image" id="image">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4 float-right">
                        Edit
                    </button>

                </form>

            </div>
        </div>
    </div>
@endsection
