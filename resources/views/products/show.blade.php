@extends('layouts.default')

@section('content')

<section class="bg-gray-7">
    <div class="breadcrumbs-custom box-transform-wrap context-dark">
      <div class="container">
        <h3 class="breadcrumbs-custom-title">Products</h3>
        <div class="breadcrumbs-custom-decor"></div>
      </div>
      <div class="box-transform" style="background-image: url({{asset('images/bg-1.jpg')}});"></div>
    </div>
    <div class="container">
      <ul class="breadcrumbs-custom-path">
        <li><a href="{{route('home')}}">Home</a></li>
        <li class="active"><a href="{{route('home')}}">Products</a></li>
        <li class="active"><a href="#">Show</a></li>
      </ul>
    </div>
  </section>

  <div class="container">
    <div class="row">
      <div class="col-md-5 offset-md-4 ">
        <div class="card mt-4 mb-4">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary"> Show Product</h6>
          </div>
          <div class="card-body">

            <div class="card ml-2">
              <img src="{{asset($product->image)}}" class="card-img-top">
              <div class="card-body">
                <h5 class="card-title">{{$product->name}}</h5>
                <h3 class="card-title">${{$product->price}}</h3>
                <p class="card-text">
                    {{$product->description}}
                </p>
                <a href="#" class="btn btn-primary mt-4">Add To Cart</a>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </div>

@endsection
