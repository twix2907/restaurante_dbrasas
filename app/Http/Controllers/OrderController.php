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

    public function proccesCheckout(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'required',
        ]);
    
        DB::transaction(function () use ($request) {
            $order = $this->createOrder($request);
            $this->attachItemsToOrder($order);
        });
    
        Cart::instance('shopping')->destroy();
    
        return redirect()->route('home')->with('msg', 'Orden creada correctamente.');
    }
    
    private function createOrder(Request $request): Order
    {
        return Order::create([
            'total'    => Cart::instance('shopping')->priceTotal(),
            'notes'    => $request->input('notes'),
            'status'   => 'Pending',
            'fecha'    => now()->toDateString(),
            'user_id'  => auth()->id(),
        ]);
    }
    
    private function attachItemsToOrder(Order $order): void
    {
        foreach (Cart::instance('shopping')->content() as $product) {
            $item = Item::create([
                'name'       => $product->name,
                'price'      => $product->price,
                'qty'        => $product->qty,
                'image'      => $product->options->image,
                'product_id' => $product->id,
                'fecha'      => now()->toDateString(),
            ]);
    
            $order->items()->attach($item->id, [
                'qty'   => $product->qty,
                'fecha' => now()->toDateString(),
            ]);
        }
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
