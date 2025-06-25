<section class="section section-lg bg-default">
    <div class="container">
      <h3 class="oh-desktop"><span class="d-inline-block wow slideInUp">Products</span></h3>
      <div class="row row-lg row-30">

        @foreach ($products as $product)

          <x-product :$product />
          
        @endforeach

      </div>
    </div>
  </section>