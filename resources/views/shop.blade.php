@extends('layouts.default')

@section('content')

<section class="bg-gray-7">
    <div class="breadcrumbs-custom box-transform-wrap context-dark">
      <div class="container">
        <h3 class="breadcrumbs-custom-title">Shop</h3>
        <div class="breadcrumbs-custom-decor"></div>
      </div>
      <div class="box-transform" style="background-image: url(images/bg-1.jpg);"></div>
    </div>
    <div class="container">
      <ul class="breadcrumbs-custom-path">
        <li><a href="#">Home</a></li>
        <li class="active"><a href="#">Shop</a></li>
      </ul>
    </div>
  </section>

  <!-- Our Shop-->
  <section class="section section-lg bg-default">
    <div class="container">
      <div class="row row-lg row-30">

        @foreach ($products as $product)

            <x-product :$product />
            
        @endforeach


      </div>
      <div class="mt-4">
        {{$products->links()}}
      </div>
    </div>
  </section>

    
@endsection