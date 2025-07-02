@extends('layouts.default')

@section('content')

<section class="bg-gray-7">
    <div class="breadcrumbs-custom box-transform-wrap context-dark">
      <div class="container">
        <h3 class="breadcrumbs-custom-title">Checkout</h3>
        <div class="breadcrumbs-custom-decor"></div>
      </div>
      <div class="box-transform" style="background-image: url(images/bg-1.jpg);"></div>
    </div>
    <div class="container">
      <ul class="breadcrumbs-custom-path">
        <li><a href="#">Home</a></li>
        <li class="active"><a href="#">Checkout</a></li>
      </ul>
    </div>
  </section>

  <div class="container mt-4 mb-4">
    <div class="row">
      <div class="col-7">
        <h3 class="mb-4">BILLING DETAILS</h3>

        <x-errors />

        <form action="{{route('orders.proccess.checkout')}}" method="POST">
            @csrf

          <div class="row row-20 gutters-20">
            <div class="col-md-6">

              <div class="form-wrap">
                <label class="form-label" for="name">Name*</label>
                <input class="form-input" id="name" type="text" value="{{auth()->user()->name ?? old('name')}}" name="name">

              </div>

            </div>
            <div class="col-md-6">

              <div class="form-wrap">
                <label class="form-label" for="last_name">Last name*</label>
                <input class="form-input" id="last_name" type="text" value="{{auth()->user()->last_name ?? old('last_name')}}" name="last_name">

              </div>

            </div>
            <div class="col-md-6">

              <div class="form-wrap">
                <label class="form-label" for="email">Email*</label>
                <input class="form-input" id="email" type="email" value="{{auth()->user()->email ?? old('email')}}" name="email">

              </div>

            </div>
            <div class="col-md-6">

              <div class="form-wrap">
                <label class="form-label" for="phone">Phone*</label>
                <input class="form-input" id="phone" type="text" value="{{auth()->user()->phone ?? old('phone')}}" name="phone">

              </div>

            </div>
            <div class="col-md-12">

              <div class="form-wrap">
                <label class="form-label" for="address">Address*</label>
                <input class="form-input" id="address" type="text" value="{{auth()->user()->address ?? old('address')}}" name="address">

              </div>

            </div>

            <div class="col-md-12">

              <label class="form-label rd-input-label" for="notes">Notes</label>

              <textarea class="form-input" id="notes" rows="5" cols="5" name="notes">{{old('notes')}}</textarea>



            </div>




          </div>
          <button type="submit" class="btn btn-primary mt-4 float-right">
            Order
          </button>
        </form>

      </div>
      <div class="col-5">
        <h3 class="mb-4">YOUR ORDER</h3>

        <x-cart />

      </div>
    </div>
  </div>
@endsection
