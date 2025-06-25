@extends('layouts.default')

@section('content')

      <!-- Swiper-->
        @include('layouts.partials.slider')
      <!-- What We Offer-->
        @include('layouts.partials.categories')

      <!-- Section CTA-->
        @include('layouts.partials.banner')

      <!-- Our Shop-->
        @include('layouts.partials.products-home')


      <!-- What We Offer-->
        {{-- @include('layouts.partials.comments') --}}

        {{-- Gallery --}}
        @include('layouts.partials.gallery')


      <!-- Section Services  Last section-->
        @include('layouts.partials.services')
@endsection
