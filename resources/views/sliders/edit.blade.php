@extends('layouts.admin')

@section('content')
<div class="row col-md-8 offset-md-2">
    <div class="card shadow">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h3 class="m-0 font-weight-bold text-primary"> Edit</h3>
            <a href="{{route('sliders.index')}}" class="btn btn-primary">Volver</a>
        </div>
        <div class="card-body">

            <x-errors />

            <form method="POST" action="{{route('sliders.update',$slider->id)}}" class="form-row" enctype="multipart/form-data">

                @csrf
                @method('PATCH')

                <div class="form-group col-md-6">
                    <label for="title">Title*</label>
                    <input class="form-control" id="title" type="text" value="{{$slider->title}}" name="title">

                </div>

                <div class="form-group col-md-6">

                    <label for="description">Description*</label>
                    <input class="form-control" id="description" type="text" value="{{$slider->description}}" name="description">

                </div>


                <div class="form-group col-md-6">
                    <label for="link">Link*</label>
                    <input class="form-control" id="link" type="text" value="{{$slider->link}}" name="link">

                </div>

                <div class="form-group col-md-6">

                    <label for="text_link">Text link*</label>
                    <input class="form-control" id="text_link" type="text" value="{{$slider->text_link}}" name="text_link">

                </div>

                <div class="col-6">
                    <img src="{{asset($slider->image)}}" width="180">
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