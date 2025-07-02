@extends('layouts.admin')

@section('content')
<div class="row col-md-8 offset-md-2">
    <div class="card shadow">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h3 class="m-0 fw-bold text-primary"> Edit</h3>
            <a href="{{route('categories.index')}}" class="btn btn-primary">Volver</a>
        </div>
        <div class="card-body">

            <x-errors />

            <form method="POST" action="{{route('categories.update',$category->id)}}" class="form-row" enctype="multipart/form-data">

                @csrf
                @method('PATCH')

                <div class="form-group col-md-6">
                    <label for="name">Name*</label>
                    <input class="form-control" id="name" type="text" value="{{$category->name}}" name="name">

                </div>

                <div class="form-group col-md-6">

                    <label for="icon">Icon*</label>
                    <input class="form-control" id="icon" type="text" value="{{$category->icon}}" name="icon">

                </div>

                <div class="col-6">
                    <img src="{{asset($category->image)}}" width="180">
                </div>

                <div class="col-6">
                    <div class="form-wrap">
                        <label for="image">Imagen</label>
                        <input type="file" class="form-control-file" name="image" id="image">
                    </div>
                </div>


                <button type="submit" class="btn btn-primary mt-4 float-right">
                    Editar
                </button>

            </form>
       

        </div>


    </div>
</div>

@endsection
