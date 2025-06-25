@extends('layouts.admin')

@section('content')
    <div class="row col-md-8 offset-md-2">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h3 class="m-0 font-weight-bold text-primary"> Create User</h3>
                <a href="{{route('users.index')}}" class="btn btn-primary">Volver</a>
            </div>
            <div class="card-body">

                <x-errors />

                <form method="POST" action="{{route('users.store')}}" class="form-row" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group col-md-6">

                        <label for="name">Name*</label>
                        <input class="form-control" id="name" type="name" value="{{old('name')}}" name="name">

                    </div>

                    <div class="form-group col-md-6">

                        <label for="last_name">Last name</label>
                        <input class="form-control" id="last_name" type="text" value="{{old('last_name')}}" name="last_name">

                    </div>

                    <div class="form-group col-md-6">

                        <label for="email">Email*</label>
                        <input class="form-control" id="email" type="email" value="{{old('email')}}" name="email">

                    </div>
                    <div class="form-group col-md-6">

                        <label for="address">Address</label>
                        <input class="form-control" id="address" type="text" value="{{old('address')}}" name="address">

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
                        <input class="form-control" id="phone" type="text" value="{{old('phone')}}" name="phone">

                    </div>
                    <div class="form-group col-md-6">

                        <div class="form-check" style="margin-top: 2rem">
                            <input class="form-check-input" type="checkbox" id="admin" name="admin" {{old('admin') ? 'checked' : ''}}>
                            <label class="form-check-label" for="admin">
                              Admin
                            </label>
                          </div>

                    </div>

                    <div class="col-12">
                        <div class="form-wrap">
                            <label for="image">Imagen</label>
                            <input type="file" class="form-control-file" name="image" id="image">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4 float-right">
                        Crear
                    </button>

                </form>

            </div>

        </div>
    </div>
@endsection
