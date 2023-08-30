@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Merchant Enquiries</h1>
                </div>
                <div class="col-sm-6">
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                    <li class="nav-item">
                        <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-list mr-2"></i>Enquiry List</a>
                    </li>
                    @include('layouts.right_toolbar', compact('dataTable'))
                </ul>
            </div>
            <div class="card-body">
                @include('merchant-requests.table')
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('merchant-requests.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Change Status</h5>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="merchantRequestId" id="merchantRequestId">
                        <div class="form-group row ">
                            {!! Form::label('tax', 'Status', ['class' => 'col-12 control-label']) !!}
                            <div class="col-9">
                                <select class="form-control" name="status">
                                    @foreach(\App\Models\MerchantRequest::$statuses as $status)
                                        <option value="{{$status}}">{{$status}}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted">
                                    select status
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts_lib')
    <script>
        $(function () {
            $('#request-table').on('click','.change-status', function(e){
                e.preventDefault();
                var id = $(this).attr('data-id');
                $("#merchantRequestId").val(id);
                $('#statusModal').modal('show');

            });
        });
    </script>
@endpush

