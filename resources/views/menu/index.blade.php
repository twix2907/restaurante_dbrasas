@extends('layouts.default')

@section('content')
<!-- Hero Section -->
<section class="section section-lg bg-primary text-white">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6">
        <h1 class="display-4 font-weight-bold mb-4">Nuestro Menú</h1>
        <p class="lead mb-4">Descubre los mejores sabores de pollo a la brasa y deliciosas bebidas</p>
        <div class="d-flex flex-wrap gap-3">
          <a href="#comidas" class="btn btn-light btn-lg">Ver Comidas</a>
          <a href="#bebidas" class="btn btn-outline-light btn-lg">Ver Bebidas</a>
        </div>
      </div>
      <div class="col-lg-6">
        <img src="{{ asset('images/menu-hero.jpg') }}" alt="Menú Pollería" class="img-fluid rounded">
      </div>
    </div>
  </div>
</section>

<!-- Search Section -->
<section class="section section-sm bg-light">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="input-group">
          <input type="text" class="form-control" id="searchInput" placeholder="Buscar en el menú...">
          <button class="btn btn-primary" type="button" id="searchBtn">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Comidas Section -->
<section id="comidas" class="section section-lg bg-default">
  <div class="container">
    <h2 class="text-center mb-5">
      <span class="d-inline-block wow slideInUp">Comidas</span>
    </h2>
    
    <div class="row row-lg row-30" id="comidas-container">
      @forelse($comidas as $product)
        <div class="col-sm-6 col-lg-3 mb-4">
          <div class="menu-item wow fadeInUp" data-wow-delay=".{{ $loop->iteration * 100 }}s">
            <div class="menu-item-image">
              <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded">
              <div class="menu-item-overlay">
                <button class="btn btn-primary btn-sm add-to-cart" 
                        data-product-id="{{ $product->id }}"
                        data-product-name="{{ $product->name }}"
                        data-product-price="{{ $product->price }}">
                  <i class="fas fa-plus"></i> Agregar
                </button>
              </div>
            </div>
            <div class="menu-item-info p-3 bg-white rounded-bottom">
              <h6 class="menu-item-title mb-2">{{ $product->name }}</h6>
              <p class="menu-item-description small text-muted mb-2">{{ Str::limit($product->description, 80) }}</p>
              <div class="d-flex justify-content-between align-items-center">
                <span class="menu-item-price text-primary font-weight-bold">${{ number_format($product->price, 2) }}</span>
                <span class="menu-item-quantity badge badge-secondary">{{ $product->unit ?? '1 Porción' }}</span>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12 text-center">
          <p class="text-muted">No hay comidas disponibles en este momento.</p>
        </div>
      @endforelse
    </div>
  </div>
</section>

<!-- Bebidas Section -->
<section id="bebidas" class="section section-lg bg-light">
  <div class="container">
    <h2 class="text-center mb-5">
      <span class="d-inline-block wow slideInUp">Bebidas</span>
    </h2>
    
    <div class="row row-lg row-30" id="bebidas-container">
      @forelse($bebidas as $product)
        <div class="col-sm-6 col-lg-3 mb-4">
          <div class="menu-item wow fadeInUp" data-wow-delay=".{{ $loop->iteration * 100 }}s">
            <div class="menu-item-image">
              <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded">
              <div class="menu-item-overlay">
                <button class="btn btn-primary btn-sm add-to-cart" 
                        data-product-id="{{ $product->id }}"
                        data-product-name="{{ $product->name }}"
                        data-product-price="{{ $product->price }}">
                  <i class="fas fa-plus"></i> Agregar
                </button>
              </div>
            </div>
            <div class="menu-item-info p-3 bg-white rounded-bottom">
              <h6 class="menu-item-title mb-2">{{ $product->name }}</h6>
              <p class="menu-item-description small text-muted mb-2">{{ Str::limit($product->description, 80) }}</p>
              <div class="d-flex justify-content-between align-items-center">
                <span class="menu-item-price text-primary font-weight-bold">${{ number_format($product->price, 2) }}</span>
                <span class="menu-item-quantity badge badge-secondary">{{ $product->unit ?? '500ml' }}</span>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12 text-center">
          <p class="text-muted">No hay bebidas disponibles en este momento.</p>
        </div>
      @endforelse
    </div>
  </div>
</section>

<!-- Call to Action -->
<section class="section section-sm bg-primary text-white">
  <div class="container text-center">
    <h3 class="mb-3">¿Listo para ordenar?</h3>
    <p class="mb-4">Revisa tu carrito y completa tu pedido</p>
    <a href="{{ route('orders.checkout') }}" class="btn btn-light btn-lg">
      <i class="fas fa-shopping-cart"></i> Ver Carrito
    </a>
  </div>
</section>

<style>
.menu-item {
  border: 1px solid #e9ecef;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  background: white;
}

.menu-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.menu-item-image {
  position: relative;
  overflow: hidden;
  height: 200px;
}

.menu-item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.menu-item:hover .menu-item-image img {
  transform: scale(1.05);
}

.menu-item-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.menu-item:hover .menu-item-overlay {
  opacity: 1;
}

.menu-item-info {
  border-top: none;
}

.menu-item-title {
  font-weight: 600;
  color: #333;
  margin-bottom: 0.5rem;
}

.menu-item-description {
  font-size: 0.875rem;
  line-height: 1.4;
}

.menu-item-price {
  font-size: 1.1rem;
}

.menu-item-quantity {
  font-size: 0.75rem;
}

.add-to-cart {
  border-radius: 20px;
  padding: 8px 16px;
  font-weight: 500;
}

.add-to-cart:hover {
  transform: scale(1.05);
}

.gap-3 {
  gap: 1rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Funcionalidad de búsqueda
  const searchInput = document.getElementById('searchInput');
  const searchBtn = document.getElementById('searchBtn');
  
  function performSearch() {
    const query = searchInput.value.trim();
    if (query.length < 2) return;
    
    fetch(`/menu/search?q=${encodeURIComponent(query)}`)
      .then(response => response.json())
      .then(data => {
        // Actualizar resultados
        updateSearchResults(data);
      })
      .catch(error => {
        console.error('Error en búsqueda:', error);
      });
  }
  
  searchBtn.addEventListener('click', performSearch);
  searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      performSearch();
    }
  });
  
  // Funcionalidad de carrito
  const addToCartButtons = document.querySelectorAll('.add-to-cart');
  
  addToCartButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      
      const productId = this.dataset.productId;
      const productName = this.dataset.productName;
      const productPrice = parseFloat(this.dataset.productPrice);
      
      // Enviar al carrito
      fetch('/menu/add-to-cart', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          product_id: productId,
          quantity: 1
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Mostrar notificación
          Swal.fire({
            title: '¡Agregado al carrito!',
            text: `${productName} ha sido agregado al carrito`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
          
          // Actualizar contador del carrito si existe
          const cartCount = document.querySelector('.cart-count');
          if (cartCount) {
            cartCount.textContent = data.cart_count;
          }
        } else {
          Swal.fire({
            title: 'Error',
            text: data.message,
            icon: 'error'
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          title: 'Error',
          text: 'Hubo un problema al agregar al carrito',
          icon: 'error'
        });
      });
    });
  });
  
  function updateSearchResults(products) {
    // Implementar actualización de resultados de búsqueda
    console.log('Resultados de búsqueda:', products);
  }
});
</script>
@endsection 