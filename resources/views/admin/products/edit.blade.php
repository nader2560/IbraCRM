@extends('admin.default')

@section('page-header')
	Product <small>{{ trans('app.update_item') }}</small>
@stop

@section('content')
	{!! Form::model($item, [
			'action' => ['ProductController@update', $item->id],
			'method' => 'put', 
			'files' => true
		])
	!!}

		@include('admin.products.form')

		<button type="submit" class="btn btn-primary">{{ trans('app.edit_button') }}</button>
		
	{!! Form::close() !!}
	
@stop
