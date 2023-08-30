@if($customFields)
<h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
<!-- Name Field -->
<div class="form-group row ">
  {!! Form::label('name', trans("lang.field_name"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.field_name_placeholder")]) !!}
    <div class="form-text text-muted">
      {{ trans("lang.field_name_help") }}
    </div>
  </div>
</div>
    <!-- Charge Field -->
<div class="form-group row ">
  {!! Form::label('charge', trans("lang.field_charge"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::text('charge', null,  ['class' => 'form-control','placeholder'=>  trans("lang.field_charge_placeholder")]) !!}
    <div class="form-text text-muted">
      {{ trans("lang.field_charge_help") }}
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



  <script>
    function showTimeDivFun() {
      var checkBox = document.getElementById("useTimeCheck");
      var timeDiv = document.getElementById("timeDiv");
      if (checkBox.checked == true){
        timeDiv.style.display = "block";
      } else {
        timeDiv.style.display = "none";
      }
    }
  </script>
<h5>Is Sloted?
  <input type="checkbox" id="useTimeCheck" name="isTimeType" onclick="showTimeDivFun()" {{isset($deliveryType->isTimeType)?($deliveryType->isTimeType=='1'?'checked':''):''}}>
</h5>


  <div id="timeDiv" style="display: {{isset($deliveryType->isTimeType)?($deliveryType->isTimeType=='1'?'block':'none'):'none'}}">
  <hr/>
  <div class="form-group row ">
    <label for="start_time" class="col-3 control-label text-right">Start At</label>
    <div class="col-9">
      <input class="form-control" name="start_time" type="time" id="start_time" value="{{isset($deliveryType->start_time)?$deliveryType->start_time:''}}">
    </div>
  </div>


  <div class="form-group row ">
    <label for="end_time" class="col-3 control-label text-right">End At</label>
    <div class="col-9">
      <input class="form-control"  name="end_time" type="time" id="end_time" value="{{isset($deliveryType->end_time)?$deliveryType->end_time:''}}">
    </div>
  </div>
    <div class="form-group row ">
      <label for="display_time_start_at" class="col-3 control-label text-right">Display Time Start At</label>
      <div class="col-9">
        <input class="form-control"
               name="display_time_start_at"
               type="time"
               id="display_time_start_at"
               value="{{isset($deliveryType->display_time_start_at)?$deliveryType->display_time_start_at:''}}">
      </div>
    </div>


    <div class="form-group row ">
      <label for="display_time_end_at" class="col-3 control-label text-right">Display Time End At</label>
      <div class="col-9">
        <input class="form-control" name="display_time_end_at" type="time" id="display_time_end_at"
               value="{{isset($deliveryType->display_time_end_at)?$deliveryType->display_time_end_at:''}}">
      </div>
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
  <button type="submit" class="btn btn-{{setting('theme_color')}}" ><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.delivery_type')}}</button>
  <a href="{!! route('deliveryTypes.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
