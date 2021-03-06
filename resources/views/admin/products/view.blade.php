@extends('admin.default')

@section('page-header')
	Product <small>{{ trans('app.view_item') }}</small>
@stop

@section('content')
	<div class="bgc-white bd bdrs-3 p-20 mB-20 row">
		<div class="col-md-4">
			<img class="img-responsive" src="{{ $item->image_path }}" alt="Picture of the product {{ $item->title }}"><hr>
			<div class="text-center"><strong>All Images</strong></div>
			@forelse($item->uploads as $upload)
				<img class="img-responsive" src="{{ $upload->image_path }}" alt="Picture of the product {{ $item->title }}"><hr>
			@empty
				No other upload.
			@endforelse
		</div>
		<div class="col-md-8">
			<div class="text-center mB-15">
				<div><span class="font-weight-bold">{{$item->title}}</span> - {{$item->printable_price}}</div>
			</div>
			<div class="font-weight-light">
				<span class="font-weight-bold">Description : </span> {{$item->description ? $item->description : "No description provided.."}}
			</div>
			<div class="font-weight-light">
				<span class="font-weight-bold">Amazon Response : <br/></span>
				@forelse($item->amazon_feeds as $amazon_feed)
					@if($amazon_feed && $amazon_feed->Message)
						@forelse($amazon_feed->Message->ProcessingReport->Result as $result)
							<strong>{{ $result->ResultCode }}</strong> : {{ $result->ResultDescription }} <br/>
						@empty
							No messages to show..
						@endforelse
					@endif
					<hr/>
				@empty
					No feeds posted on Amazon..
				@endforelse
			</div>
		</div>
	</div>
@stop
