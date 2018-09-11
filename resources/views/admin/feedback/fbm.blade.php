@extends('admin.default')

@section('css')

@endsection

@section('page-header')

@endsection

@section('content')
    <div align="center">
        <img class="imgc" src="{{asset('images/fbm_logo.png')}}" alt="facebookMarket's logo" width="230px">
        <br><br>
        <a href="{{ route('feed') }}"><b>Back to the Hub</b></a> |
        <a href="{{ route('facebook') }}"><b>Refresh Content</b></a>
    </div>
    <br><br>
@endsection

@section('js')

@endsection
