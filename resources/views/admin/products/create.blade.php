@extends('admin.default')

@section('page-header')
	Product <small>{{ trans('app.add_new_item') }}</small>
@stop

@section('content')
	{!! Form::open([
			'action' => ['ProductController@store'],
			'files' => true
		])
	!!}

		@include('admin.products.form')

		<button type="submit" class="btn btn-primary">{{ trans('app.add_button') }}</button>
		
	{!! Form::close() !!}
	
@stop
