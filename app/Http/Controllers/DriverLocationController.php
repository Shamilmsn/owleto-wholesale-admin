<?php

namespace App\Http\Controllers;

use App\DataTables\AreaDataTable;
use App\DataTables\AttributeDataTable;
use App\DataTables\CityDataTable;
use App\Http\Requests\CreateAreaRequest;
use App\Http\Requests\CreateAttributesRequest;
use App\Http\Requests\CreateCityRequest;
use App\Http\Requests\UpdateAreaRequest;
use App\Http\Requests\UpdateAttributeRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Models\Area;
use App\Models\Attribute;
use App\Models\City;
use App\Models\DriversCurrentLocation;
use App\Models\Market;
use App\Models\User;
use App\Repositories\AreaRepository;
use App\Repositories\AttributesRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CityRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\FieldRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laracasts\Flash\Flash;
use Prettus\Validator\Exceptions\ValidatorException;

class DriverLocationController extends Controller
{
    public function index()
    {
        $user = User::findOrFail(Auth::id());

        $city = City::where('id', $user->city_id)->first();

        $centerLatitude = $city->center_latitude;
        $centerLongitude = $city->center_longitude;

        $driverLocations = DriversCurrentLocation::with('driver.user')->whereHas('driver', function ($query) use ($city) {
            $query->where('available',1);
            $query->where('city_id', $city->id);
        })->get();

        $driverCurrentLocations = [];

        foreach ($driverLocations as $key => $driverLocation) {
            array_push($driverCurrentLocations, [$driverLocation->driver->user->name, $driverLocation->latitude, $driverLocation->longitude, $key+1]);
        }

//        return $driverCurrentLocations;

        return view('driver-locations.index', compact(
            'driverCurrentLocations',
            'centerLatitude',
            'centerLongitude')
        );
    }
}
