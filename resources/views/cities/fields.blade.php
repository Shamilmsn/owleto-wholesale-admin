{{--@if($customFields)--}}
{{--    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>--}}
{{--@endif--}}
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
    <!-- Name Field -->
    <div class="form-group row ">
        {!! Form::label('name', trans("lang.city_name"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.city_name_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.city_name_help") }}
            </div>
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('center_latitude', 'Latitude', ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::text('center_latitude', null,  ['class' => 'form-control','placeholder'=> 'Enter the latitude']) !!}
            <div class="form-text text-muted">
                Enter the latitude
            </div>
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('center_longitude', 'Longitude', ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::text('center_longitude', null,  ['class' => 'form-control','placeholder'=> 'Enter the longitude']) !!}
            <div class="form-text text-muted">
                Enter the longitude
            </div>
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('radius', 'Radius (KM)', ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::text('radius', null,  ['class' => 'form-control','placeholder'=> 'Enter the radius']) !!}
            <div class="form-text text-muted">
                Enter the radius
            </div>
        </div>
    </div>


</div>
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
</div>
<div class="form-group col-12 text-right">
    <button type="submit" class="btn btn-{{setting('theme_color')}}" ><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.city')}}</button>
    <a href="{!! route('cities.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
