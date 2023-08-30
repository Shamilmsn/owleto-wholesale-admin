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

class VendorLocationController extends Controller
{
    public function index()
    {
        $user = User::findOrFail(Auth::id());

        $city = City::where('id', $user->city_id)->first();

        $centerLatitude = $city->center_latitude;
        $centerLongitude = $city->center_longitude;

        $vendors = Market::where('city_id', $user->city_id)->get();

        $vendorLocations = [];

        foreach ($vendors as $key => $vendor) {
            array_push($vendorLocations, [$vendor->name, $vendor->latitude, $vendor->longitude, $key+1]);
        }

        return view('vendor-locations.index', compact(
            'vendorLocations',
            'centerLatitude',
            'centerLongitude')
        );
    }
}
