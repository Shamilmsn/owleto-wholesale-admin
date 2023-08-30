<!-- Id Field -->
<div class="form-group row col-6">
  {!! Form::label('id', 'Id:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $driver->id !!}</p>
  </div>
</div>

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

<!-- Available Field -->
<div class="form-group row col-6">
  {!! Form::label('available', 'Available:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>@if($driver->available) YES @else No @endif</p>
  </div>
</div>

<!-- Created At Field -->
<div class="form-group row col-6">
  {!! Form::label('created_at', 'Created At:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $driver->created_at !!}</p>
  </div>
</div>

<!-- Updated At Field -->
<div class="form-group row col-6">
  {!! Form::label('updated_at', 'Updated At:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $driver->updated_at !!}</p>
  </div>
</div>

