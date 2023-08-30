<div class="form-group row col-6">
    {!! Form::label('bank_name', 'Bank Name:', ['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
        <p>{!! $driverBankDetail->bank_name !!}</p>
    </div>
</div>

<div class="form-group row col-6">
    {!! Form::label('account_holder_name', 'Account Holder Name:', ['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
        <p>{!! $driverBankDetail->account_holder_name !!}</p>
    </div>
</div>

<div class="form-group row col-6">
    {!! Form::label('account_number', 'Account Number:', ['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
        <p>{!! $driverBankDetail->account_number !!}</p>
    </div>
</div>

<div class="form-group row col-6">
    {!! Form::label('ifsc_code', 'Ifsc Code:', ['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
        <p>{!! $driverBankDetail->ifsc_code !!}</p>
    </div>
</div>

