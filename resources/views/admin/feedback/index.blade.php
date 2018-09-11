@extends('admin.default')

@section('css')
    <link href="{{ asset('css/infinite-slider.css') }}" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-2.2.0.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.js"></script>
    <script src="{{ asset('js/carousel.js') }}"></script>
@endsection

@section('page-header')

@endsection

@section('content')
    <div align="center" style="font-size: 30px; font-family: 'Raleway', sans-serif;">Slide to choose which platform to
        access its panel
    </div>
    <br><br><br>
    <div class="container" align="center">
        <section class="customer-logos slider">
            <div class="slide"><a href="{{ route('ebay') }}"><img class="imgc" src="{{asset('images/ebay_logo.png')}}"
                                                              alt="ebay's logo"></a>
            </div>
            <div class="slide"><a href="{{ route('amazon') }}"><img class="imgc" src="{{asset('images/amazon_logo.png')}}"
                                                             alt="amazon's logo"></a>
            </div>
            <div class="slide"><a href="{{ route('facebook') }}"><img class="imgc" src="{{asset('images/fbm_logo.png')}}"
                                                             alt="facebook marketplace's logo"></a>
            </div>
            <div class="slide"><a href="{{ route('gumtree') }}"><img class="imgc" src="{{asset('images/gumtree_logo.png')}}"
                                                              alt="gumtree's logo"></a></div>
            <div class="slide"><a href="{{ route('google') }}"><img width="90px" src="{{asset('images/icing_logo.png')}}"
                                                               alt="google+'s logo"></a>
            </div>
        </section>
    </div>
@endsection

@section('js')

@endsection
