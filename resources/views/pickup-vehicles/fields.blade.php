<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
  <div class="form-group row ">
    {!! Form::label('name', trans("lang.pickup_name"), ['class' => 'col-3 control-label text-right']) !!}
      <div class="col-9">
      {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.pickup_name_placeholder")]) !!}
          <div class="form-text text-muted">
          {{ trans("lang.pickup_name_placeholder") }}
          </div>
      </div>
  </div>
  <div class="form-group row ">
    {!! Form::label('maximum_weight', trans("lang.pickup_maximum_weight"), ['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
      {!! Form::text('maximum_weight', null,  ['class' => 'form-control','placeholder'=>  trans("lang.pickup_maximum_weight_placeholder")]) !!}
      <div class="form-text text-muted">
        {{ trans("lang.pickup_maximum_weight_placeholder") }}
      </div>
    </div>
  </div>
    <div class="form-group row ">
        {!! Form::label('amount_per_kilometer', trans("lang.pickup_amount_per_kilometer"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::text('amount_per_kilometer', null,  ['class' => 'form-control','placeholder'=>  trans("lang.pickup_maximum_weight_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.pickup_amount_placeholder") }}
            </div>
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('base_distance', 'Distance (KM)', ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::text('base_distance', null,  ['class' => 'form-control','placeholder'=>  'Enter the base distance']) !!}
            <div class="form-text text-muted">
                Enter the base distance
            </div>
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('additional_amount', 'Additional Amount', ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::text('additional_amount', null,  ['class' => 'form-control','placeholder'=>  'Enter the additional amount']) !!}
            <div class="form-text text-muted">
                Enter the additional amount
            </div>
        </div>
    </div>
</div>
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
</div>
<!-- Submit Field -->
<div class="form-group col-12 text-right">
  <button type="submit" class="btn btn-{{setting('theme_color')}}" ><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.pickup_vehicle')}}</button>
  <a href="{!! route('attributes.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
