@extends('layouts.app')
@push('css_lib')
<!-- iCheck -->
<link rel="stylesheet" href="{{asset('plugins/iCheck/flat/blue.css')}}">
<!-- select2 -->
<link rel="stylesheet" href="{{asset('plugins/select2/select2.min.css')}}">
<!-- bootstrap wysihtml5 - text editor -->
{{--<link rel="stylesheet" href="{{asset('plugins/summernote/summernote-bs4.css')}}">--}}
{{--dropzone--}}
<link rel="stylesheet" href="{{asset('plugins/dropzone/bootstrap.min.css')}}">
@endpush
@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">{{trans('lang.category_plural')}}
{{--          <small class="ml-3 mr-3">|</small><small>{{trans('lang.category_desc')}}</small>--}}
        </h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
{{--        <ol class="breadcrumb float-sm-right">--}}
{{--          <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fa fa-dashboard"></i> {{trans('lang.dashboard')}}</a></li>--}}
{{--          <li class="breadcrumb-item"><a href="{!! route('categories.index') !!}">{{trans('lang.category_plural')}}</a>--}}
{{--          </li>--}}
{{--          <li class="breadcrumb-item active">{{trans('lang.category_edit')}}</li>--}}
{{--        </ol>--}}
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->
<div class="content">
  <div class="clearfix"></div>
  @include('flash::message')
  @include('adminlte-templates::common.errors')
  <div class="clearfix"></div>
  <div class="card">
    <div class="card-header">
      <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
        @can('categories.index')
        <li class="nav-item">
          <a class="nav-link" href="{!! route('categories.index') !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.category_table')}}</a>
        </li>
        @endcan
        @can('categories.create')
        <li class="nav-item">
          <a class="nav-link" href="{!! route('categories.create') !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.category_create')}}</a>
        </li>
        @endcan
        <li class="nav-item">
          <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-pencil mr-2"></i>{{trans('lang.category_edit')}}</a>
        </li>
      </ul>
    </div>
    <div class="card-body">
      {!! Form::model($category, ['route' => ['categories.update', $category->id], 'method' => 'patch']) !!}
      <div class="row">
        @if($customFields)
          <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
        @endif
        <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
          <!-- Name Field -->
          <div class="form-group row ">
            {!! Form::label('name', trans("lang.category_name"), ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.category_name_placeholder")]) !!}
              <div class="form-text text-muted">
                {{ trans("lang.category_name_help") }}
              </div>
            </div>
          </div>
          <!-- Sector Id Field -->
          <div class="form-group row ">
            {!! Form::label('field_id', trans("lang.product_sector_id"),['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              <select class="select2 form-control"  name="field_id" >
                @foreach($sectors as $sector)
                  <option value="{{ $sector->id }}" @if($category->field_id) {{ $sector->id == $category->field_id ? 'selected': ''}} @endif>{{ $sector->name }}</option>
                @endforeach
              </select>
              <div class="form-text text-muted">{{ trans("lang.product_sector_id_help") }}</div>
            </div>
          </div>

          @if($category->is_child)
            <div class="form-group row ">
              {!! Form::label('category', 'Category', ['class' => 'col-3 control-label text-right']) !!}
              <div class="col-9">
                <input type="text" value="{{$category->parent->name}}" readonly class="form-control">
              </div>
            </div>
          @endif


          <!-- Description Field -->
          <div class="form-group row ">
            {!! Form::label('description', trans("lang.category_description"), ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              {!! Form::textarea('description', null, ['class' => 'form-control','placeholder'=>
               trans("lang.category_description_placeholder")  ]) !!}
              <div class="form-text text-muted">{{ trans("lang.category_description_help") }}</div>
            </div>
          </div>
        </div>
        <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">

          <!-- Image Field -->
          <div class="form-group row">
            {!! Form::label('image', trans("lang.category_image"), ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              <div style="width: 100%" class="dropzone image" id="image" data-field="image">
                <input type="hidden" name="image">
              </div>
              <a href="#loadMediaModal" data-dropzone="image" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
              <div class="form-text text-muted w-50">
                {{ trans("lang.category_image_help") }}
              </div>
            </div>
          </div>
          @prepend('scripts')
            <script type="text/javascript">
              var var15866134771240834480ble = '';
              @if(isset($category) && $category->hasMedia('image'))
                      var15866134771240834480ble = {
                name: "{!! $category->getFirstMedia('image')->name !!}",
                size: "{!! $category->getFirstMedia('image')->size !!}",
                type: "{!! $category->getFirstMedia('image')->mime_type !!}",
                collection_name: "{!! $category->getFirstMedia('image')->collection_name !!}"};
              @endif
              var dz_var15866134771240834480ble = $(".dropzone.image").dropzone({
                url: "{!!url('uploads/store')!!}",
                addRemoveLinks: true,
                maxFiles: 1,
                acceptedFiles: ".png,.jpg,.jpeg",
                init: function () {
                  @if(isset($category) && $category->hasMedia('image'))
                  dzInit(this,var15866134771240834480ble,'{!! url($category->getFirstMediaUrl('image','thumb')) !!}')
                  @endif
                },
                accept: function(file, done) {
                  dzAccept(file,done,this.element,"{!!config('medialibrary.icons_folder')!!}");
                },
                sending: function (file, xhr, formData) {
                  dzSending(this,file,formData,'{!! csrf_token() !!}');
                },
                maxfilesexceeded: function (file) {
                  dz_var15866134771240834480ble[0].mockFile = '';
                  dzMaxfile(this,file);
                },
                complete: function (file) {
                  dzComplete(this, file, var15866134771240834480ble, dz_var15866134771240834480ble[0].mockFile);
                  dz_var15866134771240834480ble[0].mockFile = file;
                },
                removedfile: function (file) {
                  dzRemoveFile(
                          file, var15866134771240834480ble, '{!! url("categories/remove-media") !!}',
                          'image', '{!! isset($category) ? $category->id : 0 !!}', '{!! url("uplaods/clear") !!}', '{!! csrf_token() !!}'
                  );
                }
              });
              dz_var15866134771240834480ble[0].mockFile = var15866134771240834480ble;
              dropzoneFields['image'] = dz_var15866134771240834480ble;


            </script>
          @endprepend
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
          <button type="submit" class="btn btn-{{setting('theme_color')}}" ><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.category')}}</button>
          <a href="{!! route('categories.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
        </div>

      </div>
      {!! Form::close() !!}
      <div class="clearfix"></div>
    </div>
  </div>
</div>
@include('layouts.media_modal')
@endsection
@push('scripts_lib')
<!-- iCheck -->
<script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
<!-- select2 -->
<script src="{{asset('plugins/select2/select2.min.js')}}"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
{{--<script src="{{asset('plugins/summernote/summernote-bs4.min.js')}}"></script>--}}
{{--dropzone--}}
<script src="{{asset('plugins/dropzone/dropzone.js')}}"></script>
<script type="text/javascript">
    Dropzone.autoDiscover = false;
    var dropzoneFields = [];
</script>
@endpush