{{--@if($customFields)--}}
{{--    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>--}}
{{--@endif--}}
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
    <!-- Name Field -->
    <div class="form-group row ">
        {!! Form::label('name', trans("lang.area_name"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.area_name_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.area_name_help") }}
            </div>
        </div>
    </div>
    <!-- Sector Id Field -->
    <div class="form-group row ">
        {!! Form::label('city_id', trans("lang.city_id"),['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::select('city_id', $city, null, ['class' => 'select2 form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.city_id_help") }}</div>
        </div>
    </div>
{{--    <div class="form-group row ">--}}
{{--        {!! Form::label('covered_kilometer', trans("lang.covered_kilometer"), ['class' => 'col-3 control-label text-right']) !!}--}}
{{--        <div class="col-9">--}}
{{--            {!! Form::number('covered_kilometer', null,  ['class' => 'form-control','placeholder'=>  trans("lang.covered_kilometer_placeholder")]) !!}--}}
{{--            <div class="form-text text-muted">--}}
{{--                {{ trans("lang.covered_kilometer_help") }}--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <div class="form-group row ">--}}
{{--        {!! Form::label('address', trans("lang.address_name"), ['class' => 'col-3 control-label text-right']) !!}--}}
{{--        <div class="col-9">--}}
{{--            {!! Form::text('address', null,  ['class' => 'form-control','placeholder'=>  trans("lang.address_name_placeholder")]) !!}--}}
{{--            <div class="form-text text-muted">--}}
{{--                {{ trans("lang.address_name_help") }}--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}


</div>
{{--<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">--}}
{{--</div>--}}
{{--@if($customFields)--}}
{{--    <div class="clearfix"></div>--}}
{{--    <div class="col-12 custom-field-container">--}}
{{--        <h5 class="col-12 pb-4">{!! trans('lang.custom_field_plural') !!}</h5>--}}
{{--        {!! $customFields !!}--}}
{{--    </div>--}}
{{--@endif--}}
<!-- Submit Field -->
<div class="form-group col-12 text-right">
    <button type="submit" class="btn btn-{{setting('theme_color')}}" ><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.area')}}</button>
    <a href="{!! route('areas.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
