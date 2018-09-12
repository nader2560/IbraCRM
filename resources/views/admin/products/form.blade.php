<div class="row mB-40">
	<div class="col-sm-8">
		<div class="bgc-white p-20 bd">
			{!! Form::myInput('text', 'title', 'Title') !!}

			{!! Form::myTextArea('description', 'Description') !!}

			{!! Form::mySelectCategory('standard_product_category', 'Standard product Category') !!}

			{!! Form::mySelect('standard_product_id_type', 'Standard product id type', ["UPC", "EAN", "ISBN"]) !!}

			{!! Form::myInput('text', 'standard_product_id_code', 'Standard product id code') !!}

			{!! Form::myInput('price', 'price', 'Price') !!}

			{!! Form::myFiles('image_path', 'Image Paths') !!}

		</div>
	</div>
</div>