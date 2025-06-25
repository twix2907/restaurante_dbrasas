@extends('layouts.admin')

@section('content')
    <!-- Begin Page Content -->


        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h3 class="m-0 font-weight-bold text-primary"> Products</h3>
                <a href="{{route('products.create')}}" class="btn btn-primary">Crear</a>

            </div>
            <div class="card-body">

                <table class="table text-center">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Image</th>
                            <th scope="col">Name</th>
                            <th scope="col">Description</th>
                            <th scope="col">Price</th>
                            <th scope="col">Label</th>
                            <th scope="col">Category</th>
                            <th scope="col">...</th>
                            <th scope="col">...</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                        <tr>
                            <th scope="row">{{$product->id}}</th>
                            <td>
                                <img src="{{asset($product->image)}}" width="80">

                            </td>
                            <td>{{$product->name}}</td>
                            <td>{{$product->description}}</td>
                            <td>${{$product->price}}</td>
                            <td>{{$product->label}}</td>
                            <td>{{$product->category->name}}</td>

                            <td>
                                <a class="btn btn-primary btn-sm" href="{{route('products.edit',$product->id)}}">
                                    <span class="fas fa-edit"></span>
                                </a>
                            </td>
                            <td>

                                <form action="{{route('products.destroy',$product->id)}}" method="POST" class="confirm-form mb-0">
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

                {{$products->links()}}


            </div>
        </div>


 
    <!-- /.container-fluid -->
@endsection
