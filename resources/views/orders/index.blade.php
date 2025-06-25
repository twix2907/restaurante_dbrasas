@extends('layouts.admin')

@section('content')
<div class="card shadow">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h3 class="m-0 font-weight-bold text-primary"> Orders</h3>
    </div>
    <div class="card-body">

        <table class="table text-center">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Total</th>
                    <th scope="col">User</th>
                    <th scope="col">Date</th>
                    <th scope="col">Status</th>
                    <th scope="col">...</th>
                    <th scope="col">...</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                <tr>
                    <th scope="row">{{$order->id}}</th>
                    <td>${{$order->total}}</td>
                    <td>{{$order->user->name}}</td>
                    <td>{{$order->fecha}}</td>
                    <td>
                        <span class="@if($order->status=='Pending') badge badge-danger @else badge badge-success @endif" style="padding: 10px">
                            {{$order->status}}
                        </span>
                    </td>

                    <td>
                        <a class="btn btn-primary btn-sm" href="{{route('orders.show',$order->id)}}">
                            <span class="fas fa-eye"></span>
                        </a>
                    </td>
                    <td>

                        <form action="{{route('orders.destroy',$order->id)}}" method="POST" class="confirm-form mb-0">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger btn-sm">
                                <span class="fas fa-trash"></span>
                            </button>

                        </form>
                    </td>
                </tr>

                @empty

                <tr>
                    <td colspan="10">Sin registros</td>
                </tr>

                @endforelse


            </tbody>
        </table>
    </div>
    <div class="card-footer">

        {{$orders->links()}}


    </div>
</div>
@endsection
