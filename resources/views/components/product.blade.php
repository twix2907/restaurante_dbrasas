<div class="col-sm-6 col-lg-4 col-xl-3">
    <!-- Product-->
    <article class="product wow fadeInLeft" data-wow-delay=".15s">
      <div class="product-figure">
        <img src="{{asset($product->image)}}" alt="" width="161" height="162"/>
      </div>
      <h6 class="product-title">{{$product->name}}</h6>
      <div class="product-price-wrap">
        <div class="product-price">${{$product->price}}</div>
      </div>
      <div class="product-button">
        <div class="button-wrap">
            <a class="button button-xs button-primary button-winona" href="{{route('cart.add',$product)}}">Add to cart</a>
        </div>
        <div class="button-wrap">
            <a class="button button-xs button-secondary button-winona" href="{{route('products.display',$product->id)}}">
              View Product
            </a>
        </div>
      </div>
      @if ($product->label)
      <span class="product-badge product-badge-new">
        {{$product->label}}
      </span>
      @endif

    </article>
  </div>
