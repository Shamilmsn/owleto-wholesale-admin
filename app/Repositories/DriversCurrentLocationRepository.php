<?php

namespace App\Repositories;

use App\Models\DriversCurrentLocation;
use App\Models\Market;
use Prettus\Repository\Traits\CacheableRepository;

/**
 * Class MarketRepository
 * @package App\Repositories
 * @version August 29, 2019, 9:38 pm UTC
 *
 * @method Market findWithoutFail($id, $columns = ['*'])
 * @method Market find($id, $columns = ['*'])
 * @method Market first($columns = ['*'])
 */
class DriversCurrentLocationRepository extends BaseRepository
{

    use CacheableRepository;
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'latitude',
        'longitude',
        'driver_id',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return DriversCurrentLocation::class;
    }

}
