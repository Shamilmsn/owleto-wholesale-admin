@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Terms and Conditions</h1>
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
                        <a class="nav-link active" href="{!! url()->current() !!}">
                            <i class="fa fa-list mr-2"></i>Terms and Conditions</a>
                    </li>
                    @can('terms.create')
                        <li class="nav-item">
                            <a class="nav-link" href="{!! route('terms.create') !!}">
                                <i class="fa fa-plus mr-2"></i>Create</a>
                        </li>
                    @endcan
                    @include('layouts.right_toolbar', compact('dataTable'))
                </ul>
            </div>
            <div class="card-body table-responsive">
                @include('areas.table')
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
@endsection

