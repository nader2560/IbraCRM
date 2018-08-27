<div class="row mB-40">
	<div class="col-sm-8">
		<div class="bgc-white p-20 bd">
			{!! Form::myInput('text', 'title', 'Title') !!}

			{!! Form::myTextArea('description', 'Description') !!}

			{!! Form::myInput('price', 'price', 'Price') !!}

			{!! Form::myFile('image_path', 'Image Path') !!}

		</div>  
	</div>
</div>