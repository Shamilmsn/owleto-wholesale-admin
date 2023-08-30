@extends('layouts.app')
@push('css_lib')
    <link rel="stylesheet" href="{{asset('plugins/iCheck/flat/blue.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/select2/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/dropzone/bootstrap.min.css')}}">
    <style>
        #editorjs {
            width: 500px !important;
            border-radius: 10px!important;
            border: 1px solid #CED4DA !important;
        }
        .codex-editor__redactor {
            padding: 8px;
            border-bottom: 1px solid #ccc;
        }

        .codex-editor__redactor .codex-editor__button {
            background-color: #fff;
            border: none;
            margin-right: 6px;
            padding: 4px;
            border-radius: 2px;
            cursor: pointer;
        }
        .codex-editor__redactor .codex-editor__content {
            padding: 8px;
            border: 1px solid #ccc;
            border-top: none;
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
            min-height: 200px;
        }

    </style>
@endpush
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
        @include('adminlte-templates::common.errors')
        <div class="clearfix"></div>
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                    @can('terms.index')
                        <li class="nav-item">
                            <a class="nav-link"
                               href="{!! route('terms.index') !!}">
                                <i class="fa fa-list mr-2"></i>Terms and Conditions</a>
                        </li>
                    @endcan
                </ul>
            </div>
            <div class="card-body">
                {!! Form::open(['route' => 'terms.store']) !!}
                <div class="row">
                    <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
                        <div class="form-group row ">
                            {!! Form::label('type', 'Type',
                                ['class' => 'col-3 control-label text-right']) !!}
                            <div class="col-9">
                                {!! Form::select('type', $types, null, ['class' => 'select2 form-control']) !!}
                                <div class="form-text text-muted">Select Type</div>
                            </div>
                        </div>

                        <div class="form-group row">
                            {!! Form::label('terms', 'Terms And Conditions',
                               ['class' => 'col-3 control-label text-right']) !!}
                            <div class="col-9">
                                <div id="editorjs"></div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="editorContent" name="terms_and_conditions">
                    <div class="form-group col-12 text-right">
                        <button type="submit" class="btn btn-{{setting('theme_color')}}"><i
                                    class="fa fa-save"></i> {{trans('lang.save')}}</button>
                        <a href="{!! route('terms.index') !!}"
                           class="btn btn-default">
                            <i class="fa fa-undo"></i>
                            {{trans('lang.cancel')}}
                        </a>
                    </div>
                </div>
                {!! Form::close() !!}
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
@endsection
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const editor = new EditorJS({
            holder: 'editorjs',
            tools: {
                header: Header,
                list: List,
            },
            onChange: function () {
                editor.save().then((outputData) => {
                    const content = JSON.stringify(outputData);
                    document.getElementById('editorContent').value = content;
                });
            }
        });
    });
</script>