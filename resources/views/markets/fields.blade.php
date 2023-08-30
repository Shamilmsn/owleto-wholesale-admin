@if($customFields)
    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
    <!-- Name Field -->
    <div class="form-group row ">
        {!! Form::label('name', trans("lang.market_name"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.market_name_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.market_name_help") }}
            </div>
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('city_id', trans("lang.city_id"),['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::select('city_id', $cities, null, ['class' => 'select2 form-control', 'id' => 'city_id']) !!}
            <div class="form-text text-muted">{{ trans("lang.city_id_help") }}</div>
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('circle_id', trans("lang.area_id"),['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            <select name="circle_id" id="circle_id" class=" form-control">
            </select>
            <div class="form-text text-muted">{{ trans("lang.area_id_help") }}</div>
        </div>
    </div>


    <!-- fields Field -->
    <div class="form-group row ">
        {!! Form::label('primary_sector_id', trans("lang.primary_sector_id"),['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::select('primary_sector_id', $fields, null, ['class' => 'select2 form-control', 'id' => 'primary_sector_id']) !!}
            <div class="form-text text-muted">{{ trans("lang.primary_sector_id_help") }}</div>
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('fields[]', trans("lang.market_fields"),['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::select('fields[]', $fields, $fieldsSelected, ['class' => 'select2 form-control', 'id' => 'field_id', 'multiple'=>'multiple']) !!}
            <div class="form-text text-muted">{{ trans("lang.market_fields_help") }}</div>
        </div>

    </div>

    @hasanyrole('admin|manager')
    <!-- Users Field -->
{{--    <div class="form-group row ">--}}
{{--        {!! Form::label('drivers[]', trans("lang.market_drivers"),['class' => 'col-3 control-label text-right']) !!}--}}
{{--        <div class="col-9">--}}
{{--            {!! Form::select('drivers[]', $drivers, $driversSelected, ['class' => 'select2 form-control' , 'multiple'=>'multiple']) !!}--}}
{{--            <div class="form-text text-muted">{{ trans("lang.market_drivers_help") }}</div>--}}
{{--        </div>--}}
{{--    </div>--}}
    <!-- delivery_fee Field -->
    {{--    <div class="form-group row ">--}}
    {{--        {!! Form::label('delivery_fee', trans("lang.market_delivery_fee"), ['class' => 'col-3 control-label text-right']) !!}--}}
    {{--        <div class="col-9">--}}
    {{--            {!! Form::number('delivery_fee', null,  ['class' => 'form-control','step'=>'any','placeholder'=>  trans("lang.market_delivery_fee_placeholder")]) !!}--}}
    {{--            <div class="form-text text-muted">--}}
    {{--                {{ trans("lang.market_delivery_fee_help") }}--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--    </div>--}}

    <!-- delivery_range Field -->
    <div class="form-group row ">
        {!! Form::label('delivery_range', 'Delivery Range(KM)', ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::number('delivery_range', null,  ['class' => 'form-control', 'step'=>'any','placeholder'=>  trans("lang.market_delivery_range_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.market_delivery_range_help") }}
            </div>
        </div>
    </div>

    <!-- default_tax Field -->
    <div class="form-group row ">
        {!! Form::label('default_tax', trans("lang.market_default_tax"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::number('default_tax', null,  ['class' => 'form-control', 'step'=>'any','placeholder'=>  trans("lang.market_default_tax_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.market_default_tax_help") }}
            </div>
        </div>
    </div>

    @endhasanyrole

    <!-- Phone Field -->
    <div class="form-group row ">
        {!! Form::label('phone', trans("lang.market_phone"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::number('phone', null,  ['class' => 'form-control','placeholder'=>  trans("lang.market_phone_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.market_phone_help") }}
            </div>
        </div>
    </div>

    <!-- Mobile Field -->
    <div class="form-group row ">
        {!! Form::label('mobile', trans("lang.market_mobile"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::number('mobile', null,  ['class' => 'form-control','placeholder'=>  trans("lang.market_mobile_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.market_mobile_help") }}
            </div>
        </div>
    </div>

    <!-- Address Field -->
    <div class="form-group row ">
        {!! Form::label('address', trans("lang.market_address"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::text('address', null,  ['class' => 'form-control','placeholder'=>  trans("lang.market_address_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.market_address_help") }}
            </div>
        </div>
    </div>

    <!-- Latitude Field -->
    <div class="form-group row ">
        {!! Form::label('latitude', trans("lang.market_latitude"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::text('latitude', null,  ['class' => 'form-control','placeholder'=>  trans("lang.market_latitude_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.market_latitude_help") }}
            </div>
        </div>
    </div>

    <!-- Longitude Field -->
    <div class="form-group row ">
        {!! Form::label('longitude', trans("lang.market_longitude"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::text('longitude', null,  ['class' => 'form-control','placeholder'=>  trans("lang.market_longitude_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.market_longitude_help") }}
            </div>
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('paymetMethods[]', trans("lang.market_payment_method"),['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::select('paymetMethods[]', $paymetMethods, $paymetMethodsSelected, ['class' => 'select2 form-control' , 'multiple'=>'multiple']) !!}
            <div class="form-text text-muted">{{ trans("lang.market_payment_method_help") }}</div>
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('order_request_commission_amount', trans("lang.order_request_commission_amount"),['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::number('order_request_commission_amount', null,  ['class' => 'form-control',
                    'placeholder'=>  trans("lang.order_request_commission_amount_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.order_request_commission_amount_placeholder") }}
            </div>
        </div>
    </div>

    <!-- 'Boolean closed Field' -->
    <div class="form-group row ">
        {!! Form::label('closed', trans("lang.market_closed"),['class' => 'col-3 control-label text-right']) !!}
        <div class="checkbox icheck">
            <label class="col-9 ml-2 form-check-inline">
                {!! Form::hidden('closed', 0) !!}
                {!! Form::checkbox('closed', 1, null) !!}
            </label>
        </div>
    </div>

    <!-- 'Boolean available_for_delivery Field' -->
    <div class="form-group row ">
        {!! Form::label('available_for_delivery', trans("lang.market_available_for_delivery"),['class' => 'col-3 control-label text-right']) !!}
        <div class="checkbox icheck">
            <label class="col-9 ml-2 form-check-inline">
                {!! Form::hidden('available_for_delivery', 0) !!}
                {!! Form::checkbox('available_for_delivery', 1, null) !!}
            </label>
        </div>
    </div>
    <div id="insta-container" class="d-none">
        <div class="form-group row ">
            {!! Form::label('insta_name', trans("lang.insta_name"), ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
                {!! Form::text('insta_name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.insta_name_placeholder")]) !!}
                <div class="form-text text-muted">
                    {{ trans("lang.insta_name_help") }}
                </div>
            </div>
        </div>
        <div class="form-group row ">
            {!! Form::label('location', trans("lang.insta_location"), ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
                {!! Form::text('location', null,  ['class' => 'form-control','placeholder'=>  trans("lang.insta_location_placeholder")]) !!}
                <div class="form-text text-muted">
                    {{ trans("lang.insta_location_help") }}
                </div>
            </div>
        </div>

        <div class="form-group row ">
            {!! Form::label('about', trans("lang.insta_about"), ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
                {!! Form::textarea('about', null, ['class' => 'form-control','placeholder'=>
                 trans("lang.insta_about_placeholder")  ]) !!}
                <div class="form-text text-muted">{{ trans("lang.insta_about_help") }}</div>
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('insta_profile_pic', trans("lang.insta_profile_pic"), ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
                <div style="width: 100%" class="dropzone insta_profile_pic" id="insta_profile_pic" data-field="insta_profile_pic">
                    <input type="hidden" name="insta_profile_pic">
                </div>
                <a href="#loadMediaModal" data-dropzone="insta_profile_pic" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
                <div class="form-text text-muted w-50">
                    {{ trans("lang.insta_profile_pic_help") }}
                </div>
            </div>
        </div>
        @prepend('scripts')
            <script type="text/javascript">
                var var15671147011688676454ble = '';
                @if(isset($market) && $market->hasMedia('insta_profile_pic'))
                    var15671147011688676454ble = {
                    name: "{!! $market->getFirstMedia('insta_profile_pic')->name !!}",
                    size: "{!! $market->getFirstMedia('insta_profile_pic')->size !!}",
                    type: "{!! $market->getFirstMedia('insta_profile_pic')->mime_type !!}",
                    collection_name: "{!! $market->getFirstMedia('insta_profile_pic')->collection_name !!}"
                };
                @endif
                var dz_var15671147011688676454ble = $(".dropzone.insta_profile_pic").dropzone({
                    url: "{!!url('uploads/store')!!}",
                    addRemoveLinks: true,
                    maxFiles: 1,
                    init: function () {
                        @if(isset($market) && $market->hasMedia('insta_profile_pic'))
                        dzInit(this, var15671147011688676454ble, '{!! url($market->getFirstMediaUrl('insta_profile_pic','thumb')) !!}')
                        @endif
                    },
                    accept: function (file, done) {
                        dzAccept(file, done, this.element, "{!!config('medialibrary.icons_folder')!!}");
                    },
                    sending: function (file, xhr, formData) {
                        dzSending(this, file, formData, '{!! csrf_token() !!}');
                    },
                    maxfilesexceeded: function (file) {
                        dz_var15671147011688676454ble[0].mockFile = '';
                        dzMaxfile(this, file);
                    },
                    complete: function (file) {
                        dzComplete(this, file, var15671147011688676454ble, dz_var15671147011688676454ble[0].mockFile);
                        dz_var15671147011688676454ble[0].mockFile = file;
                    },
                    removedfile: function (file) {
                        dzRemoveFile(
                            file, var15671147011688676454ble, '{!! url("markets/remove-media") !!}',
                            'insta_profile_pic', '{!! isset($market) ? $market->id : 0 !!}', '{!! url("uplaods/clear") !!}', '{!! csrf_token() !!}'
                        );
                    }
                });
                dz_var15671147011688676454ble[0].mockFile = var15671147011688676454ble;
                dropzoneFields['insta_profile_pic'] = dz_var15671147011688676454ble;
            </script>
        @endprepend

        <div class="form-group row">
            {!! Form::label('insta_cover_pic', trans("lang.insta_cover_pic"), ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
                <div style="width: 100%" class="dropzone insta_cover_pic" id="insta_cover_pic" data-field="insta_cover_pic">
                    <input type="hidden" name="insta_cover_pic">
                </div>
                <a href="#loadMediaModal" data-dropzone="insta_cover_pic" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
                <div class="form-text text-muted w-50">
                    {{ trans("lang.insta_cover_pic_help") }}
                </div>
            </div>
        </div>
        @prepend('scripts')
            <script type="text/javascript">
                var var15671147011688676454ble = '';
                @if(isset($market) && $market->hasMedia('insta_cover_pic'))
                    var15671147011688676454ble = {
                    name: "{!! $market->getFirstMedia('insta_cover_pic')->name !!}",
                    size: "{!! $market->getFirstMedia('insta_cover_pic')->size !!}",
                    type: "{!! $market->getFirstMedia('insta_cover_pic')->mime_type !!}",
                    collection_name: "{!! $market->getFirstMedia('insta_cover_pic')->collection_name !!}"
                };
                @endif
                var dz_var15671147011688676454ble = $(".dropzone.insta_cover_pic").dropzone({
                    url: "{!!url('uploads/store')!!}",
                    addRemoveLinks: true,
                    maxFiles: 1,
                    init: function () {
                        @if(isset($market) && $market->hasMedia('insta_cover_pic'))
                        dzInit(this, var15671147011688676454ble, '{!! url($market->getFirstMediaUrl('insta_cover_pic','thumb')) !!}')
                        @endif
                    },
                    accept: function (file, done) {
                        dzAccept(file, done, this.element, "{!!config('medialibrary.icons_folder')!!}");
                    },
                    sending: function (file, xhr, formData) {
                        dzSending(this, file, formData, '{!! csrf_token() !!}');
                    },
                    maxfilesexceeded: function (file) {
                        dz_var15671147011688676454ble[0].mockFile = '';
                        dzMaxfile(this, file);
                    },
                    complete: function (file) {
                        dzComplete(this, file, var15671147011688676454ble, dz_var15671147011688676454ble[0].mockFile);
                        dz_var15671147011688676454ble[0].mockFile = file;
                    },
                    removedfile: function (file) {
                        dzRemoveFile(
                            file, var15671147011688676454ble, '{!! url("markets/remove-media") !!}',
                            'insta_cover_pic', '{!! isset($market) ? $market->id : 0 !!}', '{!! url("uplaods/clear") !!}', '{!! csrf_token() !!}'
                        );
                    }
                });
                dz_var15671147011688676454ble[0].mockFile = var15671147011688676454ble;
                dropzoneFields['insta_cover_pic'] = dz_var15671147011688676454ble;
            </script>
        @endprepend
    </div>

</div>
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">

    <!-- Image Field -->
    <div class="form-group row">
        {!! Form::label('image', trans("lang.market_image"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            <div style="width: 100%" class="dropzone image" id="image" data-field="image">
                <input type="hidden" name="image">
            </div>
            <a href="#loadMediaModal" data-dropzone="image" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
            <div class="form-text text-muted w-50">
                {{ trans("lang.market_image_help") }}
            </div>
        </div>
    </div>
    @prepend('scripts')
        <script type="text/javascript">
            var var15671147011688676454ble = '';
            @if(isset($market) && $market->hasMedia('image'))
                var15671147011688676454ble = {
                name: "{!! $market->getFirstMedia('image')->name !!}",
                size: "{!! $market->getFirstMedia('image')->size !!}",
                type: "{!! $market->getFirstMedia('image')->mime_type !!}",
                collection_name: "{!! $market->getFirstMedia('image')->collection_name !!}"
            };
                    @endif
            var dz_var15671147011688676454ble = $(".dropzone.image").dropzone({
                    url: "{!!url('uploads/store')!!}",
                    addRemoveLinks: true,
                    maxFiles: 1,
                    init: function () {
                        @if(isset($market) && $market->hasMedia('image'))
                        dzInit(this, var15671147011688676454ble, '{!! url($market->getFirstMediaUrl('image','thumb')) !!}')
                        @endif
                    },
                    accept: function (file, done) {
                        dzAccept(file, done, this.element, "{!!config('medialibrary.icons_folder')!!}");
                    },
                    sending: function (file, xhr, formData) {
                        dzSending(this, file, formData, '{!! csrf_token() !!}');
                    },
                    maxfilesexceeded: function (file) {
                        dz_var15671147011688676454ble[0].mockFile = '';
                        dzMaxfile(this, file);
                    },
                    complete: function (file) {
                        dzComplete(this, file, var15671147011688676454ble, dz_var15671147011688676454ble[0].mockFile);
                        dz_var15671147011688676454ble[0].mockFile = file;
                    },
                    removedfile: function (file) {
                        dzRemoveFile(
                            file, var15671147011688676454ble, '{!! url("markets/remove-media") !!}',
                            'image', '{!! isset($market) ? $market->id : 0 !!}', '{!! url("uplaods/clear") !!}', '{!! csrf_token() !!}'
                        );
                    }
                });
            dz_var15671147011688676454ble[0].mockFile = var15671147011688676454ble;
            dropzoneFields['image'] = dz_var15671147011688676454ble;
        </script>
@endprepend

<!-- Description Field -->
    <div class="form-group row ">
        {!! Form::label('description', trans("lang.market_description"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::textarea('description', null, ['class' => 'form-control','placeholder'=>
             trans("lang.market_description_placeholder")  ]) !!}
            <div class="form-text text-muted">{{ trans("lang.market_description_help") }}</div>
        </div>
    </div>
    <!-- Information Field -->
    <div class="form-group row ">
        {!! Form::label('information', trans("lang.market_information"), ['class' => 'col-3 control-label text-right']) !!}
        <div class="col-9">
            {!! Form::textarea('information', null, ['class' => 'form-control','placeholder'=>
             trans("lang.market_information_placeholder")  ]) !!}
            <div class="form-text text-muted">{{ trans("lang.market_information_help") }}</div>
        </div>
    </div>

</div>

@hasrole('admin')
<div class="col-12 custom-field-container">
    <h5 class="col-12 pb-4">{!! trans('lang.admin_area') !!}</h5>
    <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
        <!-- Users Field -->
        <div class="form-group row ">
            {!! Form::label('users[]', trans("lang.market_users"),['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
                {!! Form::select('users[]', $user, $usersSelected, ['class' => 'select2 form-control' , 'multiple'=>'multiple']) !!}
                <div class="form-text text-muted">{{ trans("lang.market_users_help") }}</div>
            </div>
        </div>
        
    </div>
    <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
        <!-- admin_commission Field -->
{{--        <div class="form-group row ">--}}
{{--            {!! Form::label('admin_commission', trans("lang.market_admin_commission"), ['class' => 'col-3 control-label text-right']) !!}--}}
{{--            <div class="col-9">--}}
{{--                {!! Form::number('admin_commission', null,  ['class' => 'form-control', 'step'=>'any', 'placeholder'=>  trans("lang.market_admin_commission_placeholder")]) !!}--}}
{{--                <div class="form-text text-muted">--}}
{{--                    {{ trans("lang.market_admin_commission_help") }}--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
        <div class="form-group row ">
            {!! Form::label('active', trans("lang.market_active"),['class' => 'col-3 control-label text-right']) !!}
            <div class="checkbox icheck">
                <label class="col-9 ml-2 form-check-inline">
                    {!! Form::hidden('active', 0) !!}
                    {!! Form::checkbox('active', 1, null) !!}
                </label>
            </div>
        </div>
    </div>
</div>
@endhasrole

@if($customFields)
    <div class="clearfix"></div>
    <div class="col-12 custom-field-container">
        <h5 class="col-12 pb-4">{!! trans('lang.custom_field_plural') !!}</h5>
        {!! $customFields !!}
    </div>
@endif
<!-- Submit Field -->
<div class="form-group col-12 text-right">
    <button type="submit" class="btn btn-{{setting('theme_color')}}"><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.market')}}</button>
    <a href="{!! route('markets.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
@push('scripts_lib')
    <script>

        $(document).ready(function() {

            var cityId = $('#city_id').val();

            circleByCity(cityId)

            $("#city_id").change(function () {

                var cityId = $('#city_id').val();
                circleByCity(cityId)
            });

            function circleByCity(cityId) {
                if (cityId) {
                    $.ajax({
                        url: '/circle/city/' + cityId,
                        type: "GET",
                        dataType: "json",
                        success: function (data) {
                            $('select[name="circle_id"]').empty();

                            $.each(data, function (key, value) {
                                $('#circle_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                    });
                }
            }
        });

    </script>
@endpush