<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
    <div class="form-group row ">
        {!! Form::label('order_status_id', trans("lang.order_order_status_id"),['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::select('order_status_id', $orderStatus, null, ['class' => 'select2 form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.order_order_status_id_help") }}</div>
        </div>
    </div>

{{--    <div class="form-group row ">--}}
{{--        {!! Form::label('status', trans("lang.payment_status"),['class' => 'col-3 control-label text-right']) !!}--}}
{{--        <div class="col-9">--}}
{{--            {!! Form::select('status',--}}
{{--            [--}}
{{--            'Waiting for Customer' => trans('lang.order_pending'),--}}
{{--            'Not Paid' => trans('lang.order_not_paid'),--}}
{{--            'Paid' => trans('lang.order_paid'),--}}
{{--            ]--}}
{{--            , isset($order->payment) ? $order->payment->status : '', ['class' => 'select2 form-control']) !!}--}}
{{--            <div class="form-text text-muted">{{ trans("lang.payment_status_help") }}</div>--}}
{{--        </div>--}}
{{--    </div>--}}

    <div class="form-group row ">
        {!! Form::label('tax', trans("lang.order_tax"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::number('tax', null,  ['class' => 'form-control', 'disabled' => 'disabled', 'step'=>"any",'placeholder'=>  trans("lang.order_tax_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.order_tax_help") }}
            </div>
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('delivery_fee', trans("lang.order_delivery_fee"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::number('delivery_fee', null,  ['class' => 'form-control', 'disabled' => 'disabled', 'step'=>"any",'placeholder'=>  trans("lang.order_delivery_fee_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.order_delivery_fee_help") }}
            </div>
        </div>
    </div>

{{--    <div class="form-group row ">--}}
{{--        {!! Form::label('active', trans("lang.order_active"),['class' => 'col-3 control-label text-right']) !!}--}}
{{--        <div class="checkbox icheck">--}}
{{--            <label class="col-9 ml-2 form-check-inline">--}}
{{--                {!! Form::hidden('active', 0) !!}--}}
{{--                {!! Form::checkbox('active', 1, null) !!}--}}
{{--            </label>--}}
{{--        </div>--}}
{{--    </div>--}}

</div>
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
    <div class="form-group row ">
        {!! Form::label('hint', trans("lang.order_hint"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::textarea('hint', null, ['class' => 'form-control', 'placeholder'=>
             trans("lang.order_hint_placeholder")  ]) !!}
            <div class="form-text text-muted">{{ trans("lang.order_hint_help") }}</div>
        </div>
    </div>
</div>
<div class="form-group col-12 text-right">
    <button type="submit" class="btn btn-{{setting('theme_color')}}"><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.order')}}</button>
    <a href="{!! route('orders.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
