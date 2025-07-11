@extends('layouts.admin')

@section('content')
<div class="row col-md-8 offset-md-2">
    <div class="card shadow">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h3 class="m-0 font-weight-bold text-primary"> Create</h3>
            <a href="{{route('categories.index')}}" class="btn btn-primary">Volver</a>
        </div>
        <div class="card-body">

            <x-errors />

            <form method="POST" action="{{route('categories.store')}}" class="form-row" enctype="multipart/form-data">

                @csrf

                <div class="form-group col-md-6">
                    <label for="name">Name*</label>
                    <input class="form-control" id="name" type="text" value="{{old('name')}}" name="name">

                </div>

                <div class="form-group col-md-6">

                    <label for="icon">Icon*</label>
                    <input class="form-control" id="icon" type="text" value="{{old('icon')}}" name="icon">

                </div>

                <div class="col-12">
                    <div class="form-wrap">
                        <label for="image">Imagen</label>
                        <input type="file" class="form-control-file" name="image" id="image">
                    </div>
                </div>


                <button type="submit" class="btn btn-primary mt-4 float-right">
                    Crear
                </button>

            </form>
       

        </div>


    </div>
</div>

@endsection