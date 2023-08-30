<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DriversCurrentLocation extends Model
{
    use HasFactory;

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    static function getDriverCurrentLocations($lat1, $lon1, $lat2, $lon2, $unit)
    {
        if (($lat1 == $lon1) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                return ($miles * 1.609344);
            } else if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }

    static function updateCurrentLocation($driverId, $currentDriverLatitude, $currentDriverLongitude)
    {
        $driverLocation = DriversCurrentLocation::where('driver_id', $driverId)->first();

        if (!$driverLocation) {
            $driverLocation = new DriversCurrentLocation();
        }

        $driverLocation->driver_id = $driverId;
        $driverLocation->latitude = $currentDriverLatitude;
        $driverLocation->longitude = $currentDriverLongitude;
        $driverLocation->save();

    }

    static function getAvailableDriver($latMarket, $longMarket, $market)
    {
        if (!$market) {
            $driversCurrentLocations = DriversCurrentLocation::select('drivers_current_locations.*', DB::raw("SQRT(
                POW(69.1 * (latitude - $latMarket), 10) +
                POW(69.1 * ($longMarket - longitude) * COS(latitude / 57.3), 2)) as distance"))
                ->whereHas('driver', function ($query) {
                    $query->where('available', 1);
                })
                ->orderBy('distance')
                ->with('driver')
                ->first();
        } else {

            //nearest are id taken if all drivers in market area are not available
            $areaCityId = $market->area->city_id ?? $market->city_id;
            $nearestAreaIds = Area::query()
                ->where('city_id', $areaCityId)
                ->pluck('id')->toArray();

            $driversCurrentLocations = DriversCurrentLocation::select('drivers_current_locations.*', DB::raw("SQRT(
                POW(69.1 * (latitude - $latMarket), 10) +
                POW(69.1 * ($longMarket - longitude) * COS(latitude / 57.3), 2)) as distance"))
                ->whereHas('driver', function ($query) use ($market, $nearestAreaIds) {
                    $query->where('available', 1)
                        ->where('city_id', $market->city_id)
                        ->where(function ($query) use ($market, $nearestAreaIds) {
                            $query->where('circle_id', $market->area_id)
                                ->orWhereIn('circle_id', $nearestAreaIds);
                        });
                })
                ->with(['driver.orders' => function ($query) {
                    $query
                        ->select('driver_id',
                            DB::raw('COUNT(driver_id) as total_orders_today')
                        )
                        ->groupBy('driver_id')
                        ->whereBetween('created_at', [now()->startOfDay(), now()]);
                }])
                ->orderBy('distance')
                ->with('driver')
                ->get();

            //priority goes to nearest driver, and if distance is same for multiple drivers then assigning to drivers with lease orders today
            $driversCurrentLocations = $driversCurrentLocations
                ->sortBy(function ($driversCurrentLocations) {
                    return round($driversCurrentLocations->distance);
                })
                ->sortBy(function ($driversCurrentLocations) {
                    return $driversCurrentLocations->driver->orders[0]->total_orders_today;
                })->first();

            if (!$driversCurrentLocations) {
                //taking available drivers within 1 km to market

                $driversCurrentLocations = DriversCurrentLocation::select('drivers_current_locations.*', DB::raw("SQRT(
                POW(69.1 * (latitude - $latMarket), 10) +
                POW(69.1 * ($longMarket - longitude) * COS(latitude / 57.3), 2)) as distance"))
                    ->whereHas('driver', function ($query) use ($market) {
                        $query->where('available', 1);
                    })
                    ->having('distance', '<', 1)
                    ->orderBy('distance')
                    ->with('driver')
                    ->first();

            }

        }

        return $driversCurrentLocations;
    }
}
