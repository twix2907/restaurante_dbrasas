@extends('layouts.admin')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h3 class="m-0 font-weight-bold text-primary"> Sliders</h3>
                <a href="{{route('sliders.create')}}" class="btn btn-primary">Crear</a>

            </div>
            <div class="card-body">

                <table class="table text-center">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Image</th>
                            <th scope="col">Title</th>
                            <th scope="col">Description</th>
                            <th scope="col">Link</th>
                            <th scope="col">Text Link</th>
                            <th scope="col">...</th>
                            <th scope="col">...</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sliders as $slider)
                        <tr>
                            <th scope="row">{{$slider->id}}</th>
                            <td>
                                <img src="{{asset($slider->image)}}" width="80">

                            </td>
                            <td>{{$slider->title}}</td>
                            <td>{{$slider->description}}</td>
                            <td>{{$slider->link}}</td>
                            <td>{{$slider->text_link}}</td>

                            <td>
                                <a class="btn btn-primary btn-sm" href="{{route('sliders.edit',$slider->id)}}">
                                    <span class="fas fa-edit"></span>
                                </a>
                            </td>
                            <td>

                                <form action="{{route('sliders.destroy',$slider->id)}}" method="POST" class="confirm-form mb-0">
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

                {{$sliders->links()}}


            </div>
        </div>


    </div>
    <!-- /.container-fluid -->
@endsection
