@extends('admin.default')

@section('page-header')
	Product <small>{{ trans('app.view_item') }}</small>
@stop

@section('content')
	<div class="bgc-white bd bdrs-3 p-20 mB-20 row">
		<div class="col-md-4">
			<img class="img-responsive" src="{{ $item->image_path }}" alt="Picture of the product {{ $item->title }}">
		</div>
		<div class="col-md-8">
			<div class="text-center">
				<div><span class="font-weight-bold">{{$item->title}}</span> - {{$item->printable_price}}</div>
			</div>
			<div class="font-weight-light">
				<span class="font-weight-bold">Description : </span> {{$item->description ? $item->description : "No description provided.."}}
			</div>
			{{-- Insert item specific charts etc.. --}}
		</div>
	</div>
@stop
