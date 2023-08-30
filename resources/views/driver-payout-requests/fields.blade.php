<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
    <input type="hidden" value="{{ $driverPayoutRequest->user_id }}" name="user_id">
    <input type="hidden" value="{{ $driverPayoutRequest->id }}" name="driverRequestId">
    <div class="form-group row ">
        {!! Form::label('method', trans("lang.drivers_payout_method"),['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
          {!! Form::select('method', ['bank' => 'Bank', 'cash' => 'Cash'], null, ['class' => 'select2 form-control']) !!}
          <div class="form-text text-muted">{{ trans("lang.drivers_payout_method_help") }}</div>
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('amount', trans("lang.drivers_payout_amount"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::number('amount', $driverPayoutRequest->amount-$driverPayoutRequest->paid_amount,  ['class' => 'form-control','step'=>"any", 'placeholder'=>  trans("lang.drivers_payout_amount_placeholder")]) !!}
          <div class="form-text text-muted">
            {{ trans("lang.drivers_payout_amount_help") }}
          </div>
        </div>
    </div>
</div>
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">

    <div class="form-group row ">
        {!! Form::label('note', trans("lang.drivers_payout_note"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
          {!! Form::textarea('note', null, ['class' => 'form-control','placeholder'=>
           trans("lang.drivers_payout_note_placeholder")  ]) !!}
          <div class="form-text text-muted">{{ trans("lang.drivers_payout_note_help") }}</div>
        </div>
    </div>

</div>

<div class="form-group col-12 text-right">
  <button type="submit" class="btn btn-{{setting('theme_color')}}" ><i class="fa fa-save"></i> {{trans('lang.save')}}</button>
  <a href="{!! route('driversPayouts.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
