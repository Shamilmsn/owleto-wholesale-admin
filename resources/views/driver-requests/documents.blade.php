<div class="form-group row col-12">
    {!! Form::label('pancard_number', 'PAN Number:', ['class' => 'col-2 control-label']) !!}
    <div class="col-9">
        <p>{!! $driverDocument->pancard_number !!}</p>
        <img src="{{$driverDocument->pan_image_url}}" width="200" height="200">
    </div>
</div>

<div class="form-group row col-12">
    {!! Form::label('license_number', 'License Number:', ['class' => 'col-2 control-label']) !!}
    <div class="col-9">
        <p>{!! $driverDocument->license_number !!}</p>
        <img src="{{$driverDocument->license_image_url}}" width="200" height="200">
    </div>
</div>

