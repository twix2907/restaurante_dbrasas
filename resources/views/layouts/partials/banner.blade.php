@if(isset($banner))
<section class="primary-overlay section parallax-container" data-parallax-img="{{$banner->image}}">
    <div class="parallax-content section-xl context-dark text-md-left">
      <div class="container">
        <div class="row justify-content-start">
          <div class="col-sm-8 col-md-7 col-lg-5">
            <div class="cta-modern">
              <h3 class="cta-modern-title wow fadeInRight">{{$banner->title}}</h3>
              <p class="lead">{{$banner->description}}</p>
              <a class="button button-md button-secondary-2 button-winona wow fadeInUp" href="{{$banner->link}}" data-wow-delay=".2s">{{$banner->text_link}}</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endif
