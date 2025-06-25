<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::orderBy('id','desc')->paginate(3);
        return view('users.index',compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $this->validate($request,[
            'name'=>'required|max:255',
            'email'=>'required|email|max:255',
            'password' => 'required|string|min:8|confirmed|max:255',
            'image' => 'image|mimes:jpeg,png|max:1024|nullable'
        ]);

        $user = new User();
        $user->name = $request->get('name');
        $user->last_name = $request->get('last_name');
        $user->email = $request->get('email');
        $user->address = $request->get('address');
        $user->phone = $request->get('phone');
        $user->password = Hash::make($request->get('password'));

        $user->admin = $request->get('admin') =='on' ? 1 : 0;


        // Imagen
        if ($request->hasFile("image")) {

            $imagen = $request->file("image");
            $nombreImagen='images/users/'.uniqid().'.'.$imagen->guessExtension();
            $ruta=public_path('images/users/');
            $imagen->move($ruta,$nombreImagen);
            $user->image=$nombreImagen;
        }

        $user->save();

        return redirect()->route('users.index')->with(['msg'=>'User created.']);


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('users.edit',compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->validate($request,[
            'name'=>'required',
            'email'=>'required|email',
            'password' => 'nullable|string|min:8|confirmed',
            'image' => 'image|mimes:jpeg,png|max:1024|nullable'
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->get('name');
        $user->last_name = $request->get('last_name');
        $user->email = $request->get('email');
        $user->address = $request->get('address');
        $user->phone = $request->get('phone');

        if($request->get('password')){
            $user->password = Hash::make($request->get('password'));

        }

        $user->admin = $request->get('admin') =='on' ? 1 : 0;

        // Imagen
        if ($request->hasFile("image")) {

            $path = public_path().'/'.$user->image;

            if (file_exists($path) && $user->image!=null){
                unlink($path);
            }

            $imagen = $request->file("image");
            $nombreImagen='images/users/'.uniqid().'.'.$imagen->guessExtension();
            $ruta=public_path('images/users/');
            $imagen->move($ruta,$nombreImagen);
            $user->image=$nombreImagen;
        }

        $user->update();

        return redirect()->route('users.index')->with(['msg'=>'User edit.']);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        $path = public_path().'/'.$user->image;
        if (file_exists($path) && $user->image!=null){
            unlink($path);
        }

        $user->delete();
        return redirect()->route('users.index')->with('msg','user delete.');

    }
}
