@extends('layouts.app')

@section('content')
    @include('flash::message')
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                <li class="nav-item">
                    <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-list mr-2"></i>Driver Locations</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="col-9">
                <div id="map" style="width: 1500px; height: 710px;"></div>

            </div>
        </div>
    </div>
    </div>
@endsection
@push('scripts_lib')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-locationpicker/0.1.12/locationpicker.jquery.js"></script>
<script type="text/javascript" src='https://maps.google.com/maps/api/js?libraries=places&key={{ config('services.google.map_api_key') }}' ></script>
<script>

    var locations = [];
    var drivers = @json($driverCurrentLocations);

    console.log(drivers);

    var centerLatitude = {{$centerLatitude}};
    var centerLongitude = {{$centerLongitude}};

    var arrayLength = drivers.length;

    for (var i = 0; i < arrayLength; i++) {
        locations.push(drivers[i]);
    }

    var map;
    var markers = [];

    function init()
    {
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 13,
            center: new google.maps.LatLng(centerLatitude, centerLongitude),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        var num_markers = locations.length;
        for (var i = 0; i < num_markers; i++) {  // alert(locations[i][1]+'===='+locations[i][2]+'=='+locations[i][0]);
            markers[i] = new google.maps.Marker({
                position: {lat:locations[i][1], lng:locations[i][2]},
                map: map,
                html: locations[i][0],
                id: i,
            });

            google.maps.event.addListener(markers[i], 'click', function(){
                var infowindow = new google.maps.InfoWindow({
                    id: this.id,
                    content:this.html,
                    position:this.getPosition()
                });
                google.maps.event.addListenerOnce(infowindow, 'closeclick', function(){
                    markers[this.id].setVisible(true);
                });
                this.setVisible(false);
                infowindow.open(map);
            });
        }

    }


    $( document ).ready(function() {
        google.maps.event.addDomListener(window, 'load', init);
    });
</script>

@endpush