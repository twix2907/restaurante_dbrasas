@extends('layouts.default')

@section('content')

<section class="bg-gray-7">
    <div class="breadcrumbs-custom box-transform-wrap context-dark">
      <div class="container">
        <h3 class="breadcrumbs-custom-title">
         {{$category->name}}
        </h3>
        <div class="breadcrumbs-custom-decor"></div>
      </div>
      <div class="box-transform" style="background-image: url({{asset('images/bg-1.jpg')}});"></div>
    </div>
    <div class="container">
      <ul class="breadcrumbs-custom-path">
        <li><a href="{{route('home')}}">Home</a></li>
        <li><a href="{{route('home')}}">Categories</a></li>
        <li class="active"> {{$category->name}}</li>
      </ul>
    </div>
  </section>


  <section class="section section-lg bg-default">
    <div class="container">

      <div class="row row-lg row-30">

          @foreach ($category->products as $product)

            <x-product :$product />
         
          @endforeach



      </div>
    </div>
  </section>

@endsection
