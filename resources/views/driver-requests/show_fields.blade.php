<!-- Id Field -->
<!-- User Id Field -->
<div class="form-group row col-6">
  {!! Form::label('user_id', 'Name:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $driver->user->name !!}</p>
  </div>
</div>

@if($driverPersonalDetail)
  <div class="form-group row col-6">
    {!! Form::label('email', 'Email:', ['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
      <p>{!! $driverPersonalDetail->email !!}</p>
    </div>
  </div>

  <div class="form-group row col-6">
    {!! Form::label('dob', 'DOB:', ['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
      <p>{{ \Carbon\Carbon::parse($driverPersonalDetail->date_of_birth)->format('d-m-Y') }}</p>
    </div>
  </div>

  <div class="form-group row col-6">
    {!! Form::label('address', 'Address:', ['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
      <p>{{ optional($driverPersonalDetail)->address_line_1 }},
        {{ optional($driverPersonalDetail)->city }},<br>
        {{ optional($driverPersonalDetail)->state }},
        {{ optional($driverPersonalDetail)->pincode }}
      </p>
    </div>
  </div>
@endif

<div class="form-group row col-6">
  {!! Form::label('vehicle_id', 'Vehicle:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $driver->vehicle->name !!}</p>
  </div>
</div>

<div class="form-group row col-6">
  {!! Form::label('city_id', 'City:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $driver->city->name !!}</p>
  </div>
</div>

<div class="form-group row col-6">
  {!! Form::label('circle_id', 'Area:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $driver->area->name !!}</p>
  </div>
</div>

<!-- Delivery Fee Field -->
<div class="form-group row col-6">
  {!! Form::label('delivery_fee', 'Delivery Fee:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $driver->delivery_fee !!}</p>
  </div>
</div>

<!-- Total Orders Field -->
<div class="form-group row col-6">
  {!! Form::label('total_orders', 'Total Orders:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $driver->total_orders !!}</p>
  </div>
</div>

<!-- Earning Field -->
<div class="form-group row col-6">
  {!! Form::label('earning', 'Earning:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $driver->earning !!}</p>
  </div>
</div>

{{--<!-- Available Field -->--}}
{{--<div class="form-group row col-6">--}}
{{--  {!! Form::label('available', 'Available:', ['class' => 'col-3 control-label text-right']) !!}--}}
{{--  <div class="col-9">--}}
{{--    <p>{!! $driver->available !!}</p>--}}
{{--  </div>--}}
{{--</div>--}}


