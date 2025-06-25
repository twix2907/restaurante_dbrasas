<!-- CART -->
<table class="table table-sm">
    <thead>
        <tr>
            <th scope="col">Image</th>
            <th scope="col">Name</th>
            <th scope="col">Price</th>
            <th scope="col">Qty</th>
            <th scope="col">Subtotal</th>
            <th scope="col">...</th>
        </tr>
    </thead>
    <tbody>
        @forelse (Cart::instance('shopping')->content() as $product)
        <tr>
            <td>
                <img src="{{asset($product->options->image)}}" width="40">
            </td>
            <td>{{$product->name}}</td>
            <td>${{$product->price}}</td>
            <td>{{$product->qty}}</td>
            <td>${{$product->price*$product->qty}}</td>
            <td>
                <a href="{{route('cart.remove',$product->rowId)}}"><span class="fas fa-trash"></span></a>
            </td>
        </tr>


        @empty

        <tr>
            <td>Sin productos</td>
        </tr>

        @endforelse
        <tr>
            <td colspan="3"></td>
            <td>
                Total
            </td>
            <td>${{Cart::instance('shopping')->priceTotal()}}</td>
        </tr>

    </tbody>
</table>
