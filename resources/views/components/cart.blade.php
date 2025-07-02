<!-- CART -->
<div class="cart-container">
    @if(Cart::instance('shopping')->content()->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="text-center">Imagen</th>
                        <th scope="col">Producto</th>
                        <th scope="col" class="text-center">Precio</th>
                        <th scope="col" class="text-center">Cantidad</th>
                        <th scope="col" class="text-center">Subtotal</th>
                        <th scope="col" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (Cart::instance('shopping')->content() as $product)
                    <tr>
                        <td class="text-center">
                            <img src="{{ asset($product->options->image) }}" 
                                 alt="{{ $product->name }}" 
                                 class="img-thumbnail" 
                                 style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td>
                            <strong>{{ $product->name }}</strong>
                            @if($product->options->description)
                                <br><small class="text-muted">{{ Str::limit($product->options->description, 50) }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-primary">${{ number_format($product->price, 2) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $product->qty }}</span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-success">${{ number_format($product->price * $product->qty, 2) }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('cart.remove', $product->rowId) }}" 
                               class="btn btn-sm btn-outline-danger"
                               data-bs-toggle="tooltip" 
                               title="Eliminar del carrito">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Total:</td>
                        <td class="text-center">
                            <span class="fw-bold text-success fs-5">${{ number_format(Cart::instance('shopping')->priceTotal(), 2) }}</span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="d-grid gap-2 mt-3">
            <a href="{{ route('orders.checkout') }}" 
               class="btn btn-primary btn-lg">
                <i class="fas fa-credit-card me-2"></i>Proceder al Pago
            </a>
        </div>
    @else
        <div class="text-center py-4">
            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Tu carrito está vacío</h5>
            <p class="text-muted">Agrega algunos productos para comenzar a comprar</p>
            <a href="{{ route('shop') }}" class="btn btn-primary">
                <i class="fas fa-store me-2"></i>Ir a la Tienda
            </a>
        </div>
    @endif
</div>

<style>
.cart-container {
    max-height: 400px;
    overflow-y: auto;
}

.cart-container::-webkit-scrollbar {
    width: 6px;
}

.cart-container::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.cart-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.cart-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
