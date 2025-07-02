@extends('layouts.default')

@section('content')
    <div class="container mt-4 mb-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <h4 class="card-header">Register</h4>
                    <x-errors />

                    <div class="card-body">
                        <form method="POST" action="{{ route('register') }}" class="rd-form">
                            @csrf

                            <div class="row row-20 gutters-20">

                                <div class="col-md-6">

                                    <div class="form-wrap">
                                        <label class="form-label" for="name">Name*</label>
                                        <input class="form-input" id="name" type="text" value="{{ old('name') }}"
                                            id="name" name="name">

                                    </div>

                                </div>

                                <div class="col-md-6">
                                    <div class="form-wrap">
                                        <label for="email" class="form-label">Email Address*</label>
                                        <input id="email" type="email" class="form-input" name="email"
                                            value="{{ old('email') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-wrap">
                                        <label for="password" class="form-label">Password</label>

                                        <input id="password" type="password" class="form-input" name="password">

                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-wrap">
                                        <label for="password-confirm" class="form-label">Confirm Password</label>


                                        <input id="password-confirm" type="password" class="form-input"
                                            name="password_confirmation">
                                    </div>
                                </div>

                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        Register
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
