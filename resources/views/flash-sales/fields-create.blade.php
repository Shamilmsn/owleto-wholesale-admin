<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="form column">
    <div class="form-group row ">
        {!! Form::label('product_id', 'Product*',['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            <select class="form-control select2 " id="product_id" name="product_id">
                @foreach($products as $product)
                    <option value="{{$product->id}}">{{$product->name}}</option>
                @endforeach
            </select>
            <div class="form-text text-muted">Select Product</div>
        </div>
    </div>

    <div class="form-group row ">
        <div class="input-group mb-3">
            {!! Form::label('flash_sale_start_time', trans("lang.product_flash_sale_start_time"),['class' => 'col-3 control-label text-right ']) !!}
            <div class="col-6">
                <input type="datetime-local" name="flash_sale_start_time"  class="form-control @error('flash_sale_start_time')
                        is-invalid @enderror" id="flash_sale_start_time" value="{{old('flash_sale_start_time')}}">
            </div>
        </div>
    </div>
    <div class="form-group row ">
        <div class="input-group mb-3">
            {!! Form::label('flash_sale_end_time', trans("lang.product_flash_sale_end_time"),['class' => 'col-3 control-label text-right ']) !!}
            <div class="col-6">
                <input type="datetime-local" name="flash_sale_end_time"  class="form-control @error('flash_sale_end_time')
                        is-invalid @enderror" id="flash_sale_end_time" value="{{old('flash_sale_end_time')}}">
            </div>
        </div>
    </div>
    <div class="form-group row ">
        <div class="input-group row ">
            {!! Form::label('flash_sale_price', trans("lang.product_flash_sale_price"),['class' => 'col-3 control-label text-right ']) !!}
            <div class="col-6 ml-2">
                {!! Form::number('flash_sale_price', null, ['class' => 'form-control','id' => 'flash_sale_price']) !!}
            </div>
        </div>
    </div>
</div>

<div class="form-group col-12 text-right">
    <button type="submit" class="btn btn-{{setting('theme_color')}} "
            id="button-submit"><i class="fa fa-save"></i> {{trans('lang.save')}}
        {{trans('lang.product')}}</button>
    <a href="{!! route('products.index') !!}" class="btn btn-default">
        <i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>

@push('scripts_lib')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/additional-methods.js"> </script>
    <script>
        $(document).ready(function() {
            $('#form-create').validate({
                rules: {
                    'product_id': {
                        required: true,
                    },
                },
            });
        });
    </script>
@endpush
