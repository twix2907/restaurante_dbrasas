@extends('layouts.default')

@section('content')

<section class="bg-gray-7">
    <div class="breadcrumbs-custom box-transform-wrap context-dark">
      <div class="container">
        <h3 class="breadcrumbs-custom-title">My Orders</h3>
        <div class="breadcrumbs-custom-decor"></div>
      </div>
      <div class="box-transform" style="background-image: url({{asset('images/bg-1.jpg')}});"></div>
    </div>
    <div class="container">
      <ul class="breadcrumbs-custom-path">
        <li><a href="{{route('home')}}">Home</a></li>
        <li class="active"><a href="{{route('orders.my')}}">Orders</a></li>
      </ul>
    </div>
  </section>

  <div class="container mb-4">

    <!-- Dropdown Card Example -->
    <div class="card mb-4 mt-4">
      <!-- Card Header - Dropdown -->
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 fw-bold text-primary">Orders</h6>

      </div>
      <!-- Card Body -->
      <div class="card-body">

        <table class="table text-center">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Total</th>
              <th scope="col">Date</th>
              <th scope="col">Status</th>

            </tr>
          </thead>
          <tbody>
            @foreach ($orders as $order)
            <tr>
                <th>{{$order->id}}</th>
                <td>${{$order->total}}</td>
                <td>{{$order->fecha}}</td>
                <td>
                  <span class="@if($order->status=='Pending') badge-danger @else badge-success @endif">
                    {{$order->status}}
                  </span>
                </td>

              </tr>
            @endforeach


          </tbody>
        </table>
        <!-- Paginacion -->
      </div>
    </div>

  </div>
@endsection
