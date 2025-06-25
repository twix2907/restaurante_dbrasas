<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sliders = Slider::orderBy('id','desc')->paginate(5);
        // $sliders = collect();
        return view('sliders.index',compact('sliders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sliders.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'title' => 'required|max:255',
            'description' => 'required|max:255',
            'link' => 'max:255',
            'text_link' => 'max:255',
            'image' => 'image|mimes:jpeg,png|max:1024|required'

        ]);

        $slider = new Slider();
        $slider->title = $request->get('title');
        $slider->description = $request->get('description');
        $slider->link = $request->get('link');
        $slider->text_link = $request->get('text_link');

        if($request->hasFile('image')){

            $imagen = $request->file('image');
            $nameImage = "images/sliders/".uniqid().'.'.$imagen->guessExtension();
            $ruta = public_path("images/sliders/");
            $imagen->move($ruta,$nameImage);
            $slider->image = $nameImage;

        }

        $slider->save();

        return redirect()->route('sliders.index')->with(["msg"=>"Slider creado correctamente"]);


        

    }

    /**
     * Display the specified resource.
     */
    public function show(Slider $slider)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Slider $slider)
    {
        return view('sliders.edit',compact('slider'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Slider $slider)
    {
        $this->validate($request,[
            'title' => 'required|max:255',
            'description' => 'required|max:255',
            'link' => 'max:255',
            'text_link' => 'max:255',
            'image' => 'image|mimes:jpeg,png|max:1024|nullable'

        ]);

        // $slider = new Slider();
        $slider->title = $request->get('title');
        $slider->description = $request->get('description');
        $slider->link = $request->get('link');
        $slider->text_link = $request->get('text_link');

        if($request->hasFile('image')){

            $path = public_path().'/'.$slider->image;

            if (file_exists($path) && $slider->image!==null) {
                unlink($path);
            }

            $imagen = $request->file('image');
            $nameImage = "images/sliders/".uniqid().'.'.$imagen->guessExtension();
            $ruta = public_path("images/sliders/");
            $imagen->move($ruta,$nameImage);
            $slider->image = $nameImage;

        }

        $slider->update();

        return redirect()->route('sliders.index')->with(["msg"=>"Slider editado correctamente"]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Slider $slider)
    {
        $path = public_path().'/'.$slider->image;

        if (file_exists($path) && $slider->image!==null) {
            unlink($path);
        }

        $slider->delete();

        return redirect()->route('sliders.index')->with(["msg"=>"Slider eliminado correctamente"]);
    }
}
