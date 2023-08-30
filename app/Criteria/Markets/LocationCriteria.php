<?php
/**
 * File name: NearCriteria.php
 * Last modified: 2020.05.03 at 10:15:14
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Criteria\Markets;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class NearCriteria.
 *
 * @package namespace App\Criteria\Markets;
 */
class LocationCriteria implements CriteriaInterface
{

    /**
     * @var array
     */
    private $request;
    private $cityLatitude;
    private $cityLongitude;
    private $radius;

    /**
     * NearCriteria constructor.
     */
    public function __construct(Request $request, $cityLatitude, $cityLongitude, $radius)
    {
        $this->request = $request;
        $this->cityLatitude = $cityLatitude;
        $this->cityLongitude = $cityLongitude;
        $this->radius = $radius;
    }

    /**
     * Apply criteria in query repository
     *
     * @param string $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $areaLat = $this->cityLatitude;
        $areaLon = $this->cityLongitude;
        $radius =  $this->radius;

        return $model->select("markets.*"
            ,DB::raw("6371 * acos(cos(radians(" . $areaLat . ")) 
                    * cos(radians(markets.latitude)) 
                    * cos(radians(markets.longitude) - radians(" . $areaLon . ")) 
                    + sin(radians(" .$areaLat. ")) 
                    * sin(radians(markets.latitude))) AS distance"))
            ->having('distance', '<', $radius);

    }
}
