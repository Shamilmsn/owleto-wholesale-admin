@if($customFields)
    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">

    <!-- Name Field -->
    <div class="form-group row ">
        {!! Form::label('name', trans("lang.package_name"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.package_name_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.package_name_help") }}
            </div>
        </div>
    </div>

    <!-- Quantity Field -->
    <div class="form-group row ">
        {!! Form::label('quantity', trans("lang.package_quantity"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::number('quantity', null,  ['class' => 'form-control','placeholder'=>  trans("lang.package_quantity_placeholder"), 'step'=>"any", 'min'=>"0"]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.package_quantity_help") }}
            </div>
        </div>
    </div>

    <!-- Days Field -->
    <div class="form-group row ">
        {!! Form::label('days', trans("lang.package_days"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::number('days', null,  ['class' => 'form-control','placeholder'=>  trans("lang.package_days_placeholder"),'step'=>"any", 'min'=>"0"]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.package_days_help") }}
            </div>
        </div>
    </div>

{{--Delivery Time--}}
    <div class="form-group row ">
        {!! Form::label('delivery_time', trans("lang.package_delivery_time"),['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::select('delivery_time', $deliveryTime, null, ['class' => 'select2 form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.package_delivery_time_help") }}</div>
        </div>
    </div>

<!-- User Id Field -->
    <div class="form-group row ">
        {!! Form::label('user_id', trans("lang.package_market_id"),['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            <select  name="user_id" id="market_id" class=" form-control">
                @foreach ($markets as  $key => $market)
                    <option value="{{ $key }}" {{$package->user_id == $key ? 'selected' : ''}}>{{ $market}}</option>
                @endforeach
            </select>

            <div class="form-text text-muted">{{ trans("lang.package_market_id_help") }}</div>
        </div>
    </div>

    <!-- Product Id Field -->
    <div class="form-group row ">
        {!! Form::label('product_id', trans("lang.package_product_id"),['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
{{--            {!! Form::select('product_id', $product, null, ['class' => 'select2 form-control']) !!}--}}
            <select name="product_id" id="product_id" class=" form-control">
            </select>
            <div class="form-text text-muted">{{ trans("lang.package_product_id_help") }}</div>
        </div>
    </div>


</div>
@if($customFields)
    <div class="clearfix"></div>
    <div class="col-12 custom-field-container">
        <h5 class="col-12 pb-4">{!! trans('lang.custom_field_plural') !!}</h5>
        {!! $customFields !!}
    </div>
@endif
<!-- Submit Field -->
<div class="form-group col-12 text-right">
    <button type="submit" class="btn btn-{{setting('theme_color')}}"><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.package')}}</button>
    <a href="{!! route('packages.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>

