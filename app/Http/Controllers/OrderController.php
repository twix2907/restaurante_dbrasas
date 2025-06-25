<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Gloudemans\Shoppingcart\Facades\Cart;

class OrderController extends Controller
{
    public function checkout()
    {
        return view('orders.checkout');
    }

    public function proccesCheckout(Request $request){
        // dd('proccesCheckout');
        $this->validate($request,[
            'name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'required',
        ]);

        DB::transaction(function () use($request) {

            $order = New Order();
            $order->total = Cart::instance('shopping')->priceTotal();
            $order->notes = $request->get('notes');
            $order->status = "Pending";
            $order->fecha = date('Y-m-d');
            $order->user_id = auth()->user()->id;
            $order->save();

            //agregar items

            foreach(Cart::instance('shopping')->content() as $product){
                $item = new Item();
                $item->name = $product->name;
                $item->price = $product->price;
                $item->qty = $product->qty;
                $item->image = $product->options->image;
                $item->product_id = $product->id;
                $item->fecha = date('Y-m-d');
                $item->save();

                $order->items()->attach($item->id,['qty'=>$product->qty,'fecha'=>date('Y-m-d')]);

            }
        });

        Cart::instance('shopping')->destroy();

        return redirect()->route('home')->with(['msg'=>'Orden creada correctamente.']);
    }

    public function myOrders(){
        $orders = auth()->user()->orders()->paginate(5);

        return view('orders.my-orders',compact('orders'));
    }


    public function changeStatus(Order $order){

        if ($order->status==='Pending') {
           $order->status = 'Complete';
           $order->update();
        }else{
            $order->status = 'Pending';
            $order->update();
        }

        return redirect()->back()->with(['msg'=>'Status edit']);
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::orderBy('id','desc')->paginate(5);

        return view('orders.index',compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return view('orders.show',compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        foreach($order->items as $item){
            $item->delete();

        }
        $order->delete();

        return redirect()->route('orders.index')->with(['msg'=>'Order delete.']);
    }
}
