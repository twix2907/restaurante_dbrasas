@extends('layouts.default')

@section('content')
<div class="container mt-4 mb-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <h4 class="card-header">Login</h4>

                <x-errors />

                <div class="card-body">
                    <form class="rd-form" method="POST" action="{{ route('login') }}">
                        @csrf
                       
                        <div class="row row-20 gutters-20">


                            <div class="col-md-6">
                                <div class="form-wrap">
                                    <input class="form-input"  type="email" name="email" value="{{ old('email') }}" id="email" autocomplete="email" autofocus>
                                    <label class="form-label" for="email">Your E-mail*</label>
  
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-wrap">
                                    <input class="form-input" type="password"  name="password" id="password">
                                    <label class="form-label" for="password">Your Password*</label>
   
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-wrap">
                                    <div class="form-check ml-4">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
    
                                        <label class="form-check-label" for="remember">
                                            Remember Me
                                        </label>
                                    </div>
                            
                                </div>
                            </div>


                        </div>
                        <button class="button button-secondary button-winona" type="submit">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection