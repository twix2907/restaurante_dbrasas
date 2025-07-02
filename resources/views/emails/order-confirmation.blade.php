<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmación de Pedido - D Brasas y Carbon</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .order-details { background: #f8f9fa; padding: 15px; margin: 20px 0; }
        .item { border-bottom: 1px solid #ddd; padding: 10px 0; }
        .total { font-weight: bold; font-size: 18px; text-align: right; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Gracias por tu pedido!</h1>
            <p>D Brasas y Carbon</p>
        </div>

        <div class="content">
            <h2>Hola {{ $order->user->name }},</h2>
            
            <p>Hemos recibido tu pedido y estamos preparándolo. Aquí están los detalles:</p>

            <div class="order-details">
                <h3>Pedido #{{ $order->id }}</h3>
                <p><strong>Fecha:</strong> {{ $order->fecha }}</p>
                <p><strong>Estado:</strong> {{ $order->status }}</p>
                
                <h4>Productos:</h4>
                @foreach($order->items as $item)
                <div class="item">
                    <strong>{{ $item->name }}</strong><br>
                    Cantidad: {{ $item->pivot->qty }} | Precio: ${{ $item->price }}
                </div>
                @endforeach
                
                <div class="total">
                    Total: ${{ $order->total }}
                </div>
            </div>

            @if($order->notes)
            <p><strong>Notas:</strong> {{ $order->notes }}</p>
            @endif

            <p>Te notificaremos cuando tu pedido esté listo para recoger.</p>
            
            <p>¡Gracias por elegir D Brasas y Carbon!</p>
        </div>

        <div class="footer">
            <p>D Brasas y Carbon<br>
            Teléfono: +1 718-999-3939<br>
            Email: info@dbrasasycarbon.com</p>
        </div>
    </div>
</body>
</html> 
