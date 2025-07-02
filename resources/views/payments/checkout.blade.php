@extends('layouts.default')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Procesar Pago - Pedido #{{ $order->id }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Detalles del Pedido</h5>
                            <p><strong>Total:</strong> ${{ $order->total }}</p>
                            <p><strong>Fecha:</strong> {{ $order->fecha }}</p>
                            
                            <h6>Productos:</h6>
                            <ul class="list-unstyled">
                                @foreach($order->items as $item)
                                <li>{{ $item->name }} - Qty: {{ $item->pivot->qty }} - ${{ $item->price }}</li>
                                @endforeach
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Información de Pago</h5>
                            <form action="{{ route('payment.process', $order) }}" method="POST" id="payment-form">
                                @csrf
                                
                                <div class="form-group">
                                    <label>Método de Pago</label>
                                    <select name="payment_method" class="form-control" required>
                                        <option value="">Seleccionar método</option>
                                        <option value="card">Tarjeta de Crédito/Débito</option>
                                        <option value="paypal">PayPal</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Número de Tarjeta</label>
                                    <input type="text" name="card_number" class="form-control" placeholder="1234 5678 9012 3456">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha de Expiración</label>
                                            <input type="text" name="expiry" class="form-control" placeholder="MM/YY">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>CVV</label>
                                            <input type="text" name="cvv" class="form-control" placeholder="123">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Nombre en la Tarjeta</label>
                                    <input type="text" name="card_name" class="form-control" placeholder="Nombre Apellido">
                                </div>

                                <input type="hidden" name="stripeToken" value="tok_visa">

                                <button type="submit" class="btn btn-primary btn-block">
                                    Pagar ${{ $order->total }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('payment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Aquí iría la integración con Stripe
    // Por ahora simulamos el pago exitoso
    this.submit();
});
</script>
@endsection 
