<!-- Id Field -->
@push('css_lib')
  <!-- iCheck -->
  <link rel="stylesheet" href="{{asset('plugins/iCheck/flat/blue.css')}}">

  <!-- bootstrap wysihtml5 - text editor -->
  <link rel="stylesheet" href="{{asset('plugins/select2/select2.min.css')}}">
  <link rel="stylesheet" href="{{asset('plugins/summernote/summernote-bs4.css')}}">
  {{--dropzone--}}
  <link rel="stylesheet" href="{{asset('plugins/dropzone/bootstrap.min.css')}}">
@endpush

<div class="form-group row col-6">
  {!! Form::label('id', 'Id:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $orderRequest->id !!}</p>
  </div>
</div>

<div class="form-group row col-6 ">
  {!! Form::label('vendor', 'Vendor:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $orderRequest->market->name !!}</p>
  </div>
</div>

<!-- Status Field -->
<div class="form-group row col-6">
  {!! Form::label('status', 'Status:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $orderRequest->status !!}</p>
  </div>
</div>


<!-- Created At Field -->
<div class="form-group row col-6 ">
  {!! Form::label('created_at', 'Created At:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! \Carbon\Carbon::parse($orderRequest->created_at)->format('d M Y') .' : '. \Carbon\Carbon::parse($orderRequest->created_at)->toTimeString()  !!}</p>
  </div>
</div>

@if($imageSrc)
  <div class="form-group row col-6 justify-content-center">
    <div class="col-9">
      <p><img src="{{ $imageSrc }}" width="400" height="400"></p>
    </div>
  </div>
@endif
{{--@if($orderRequest->type == 2 && $imageSrc == null)--}}

@if($orderRequest->order_text)
  <div class="form-group row col-6">
    {!! Form::label('description', 'Description:', ['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
      <p>{!! $orderRequest->order_text !!}</p>
    </div>
  </div>
@endif

@push('scripts_lib')
  <!-- iCheck -->
  <script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
  <!-- select2 -->
  <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
  <script src="{{asset('plugins/select2/select2.min.js')}}"></script>
  <script src="{{asset('plugins/summernote/summernote-bs4.min.js')}}"></script>
  {{--dropzone--}}
  <script src="{{asset('plugins/dropzone/dropzone.js')}}"></script>
  <script type="text/javascript">
    Dropzone.autoDiscover = false;
    var dropzoneFields = [];
  </script>
@endpush

