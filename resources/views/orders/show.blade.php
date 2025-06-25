@extends('layouts.admin')

@section('content')
<!-- Dropdown Card Example -->
<div class="card shadow mb-4">
    <!-- Card Header - Dropdown -->
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h3 class="m-0 font-weight-bold text-primary">
            Order#{{$order->id}}
            <span class="badge badge-pill @if($order->status=='Pending')badge-danger @else badge-success @endif">
                {{$order->status}}
            </span>
        </h3>

        <div class="d-flex flex-row align-items-end">
            <a href="{{route('orders.index')}}" class="btn btn-primary mr-2">Back</a>

            <a href="{{route('orders.status',$order)}}" class="btn btn-success">
                Change status
            </a>

        </div>
    </div>
    <!-- Card Body -->
    <div class="card-body">

        <!-- Dropdown Card Example -->
        <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Client</h6>

            </div>
            <!-- Card Body -->
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Last name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Address</th>
                            <th scope="col">Phone</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td>{{$order->user->name}}</td>
                            <td>{{$order->user->last_name}}</td>
                            <td>{{$order->user->email}}</td>
                            <td>{{$order->user->address}}</td>
                            <td>{{$order->user->phone}}</td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
        <!-- Card order -->
        <div class="card shadow mb-4">
            <!-- Card Header -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Order Details</h6>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <table class="table text-center">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Image</th>
                            <th scope="col">Name</th>
                            <th scope="col">Price</th>
                            <th scope="col">Qty</th>
                            <th scope="col">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                        <tr>
                            <td>{{$item->id}}</td>
                            <th>
                                <img src="{{asset($item->image)}}" width="50">

                            </th>
                            <td>{{$item->name}}</td>
                            <td>${{$item->price}}</td>
                            <td>
                                <span class="badge badge-pill badge-primary">
                                    {{$item->qty}}
                                </span>
                            </td>
                            <td>
                                ${{$item->price*$item->qty}}
                            </td>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="4"></td>
                            <td>Total:</td>
                            <td>${{$order->total}}</td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <!-- Card  -->
        <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Order Notes</h6>

            </div>
            <!-- Card Body -->
            <div class="card-body">

                {{$order->notes}}


            </div>
        </div>

    </div>
</div>
@endsection
