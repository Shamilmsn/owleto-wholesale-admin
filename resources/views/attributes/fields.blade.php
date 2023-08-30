@if($customFields)
<h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
<!-- Name Field -->
<div class="form-group row ">
  {!! Form::label('name', trans("lang.attribute_name"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.attribute_name_placeholder")]) !!}
    <div class="form-text text-muted">
      {{ trans("lang.attribute_name_help") }}
    </div>
  </div>
</div>

  <!-- Sector Id Field -->
  <div class="form-group row ">
    {!! Form::label('sector_id', trans("lang.sector_id"),['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
      {!! Form::select('sector_id', $sector, null, ['class' => 'select2 form-control']) !!}
      <div class="form-text text-muted">{{ trans("lang.sector_id_help") }}</div>
    </div>
  </div>
  <div class="form-group row ">
    {!! Form::label('meta', trans("lang.widget_type"),['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
      {!! Form::select('meta', $meta, null, ['class' => 'select2 form-control']) !!}
      <div class="form-text text-muted">{{ trans("lang.widget_type_help") }}</div>
    </div>
  </div>
</div>
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
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
  <button type="submit" class="btn btn-{{setting('theme_color')}}" ><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.attribute')}}</button>
  <a href="{!! route('attributes.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
