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
class NearCriteria implements CriteriaInterface
{

    /**
     * @var array
     */
    private $request;
    private $cityLatitude;
    private $cityLongitude;

    /**
     * NearCriteria constructor.
     */
    public function __construct(Request $request, $cityLatitude, $cityLongitude)
    {
        $this->request = $request;
        $this->cityLatitude = $cityLatitude;
        $this->cityLongitude = $cityLongitude;
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
        if ($this->request->has(['myLon', 'myLat'])) {

            $myLat = $this->request->get('myLat');
            $myLon = $this->request->get('myLon');
            $areaLat = $this->cityLatitude;
            $areaLon = $this->cityLongitude;
//            $radius =  $this->radius;

            return $model->select(DB::raw("SQRT(
                POW(69.1 * (latitude - $myLat), 2) +
                POW(69.1 * ($myLon - longitude) * COS(latitude / 57.3), 2)) AS distance, SQRT(
                POW(69.1 * (latitude - $areaLat), 2) +
                POW(69.1 * ($areaLon - longitude) * COS(latitude / 57.3), 2))  AS area"), "markets.*")
                ->orderBy('closed')
                ->orderBy('area');
        } else {
            return $model->orderBy('closed');
        }
    }
}
