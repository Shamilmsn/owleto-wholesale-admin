<?php
/**
 * File name: MarketAPIController.php
 * Last modified: 2020.05.04 at 09:04:19
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API;


use App\Criteria\Markets\ActiveCriteria;
use App\Criteria\Markets\LocationCriteria;
use App\Criteria\Markets\MarketOfCategoryCriteria;
use App\Criteria\Markets\MarketsOfFieldsCriteria;
use App\Criteria\Markets\NearCriteria;
use App\Criteria\Markets\PopularCriteria;
use App\Criteria\Markets\SearchCriteria;
use App\Criteria\Markets\SectorCriteria;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Field;
use App\Models\Market;
use App\Repositories\CategoryRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\MarketRepository;
use App\Repositories\UploadRepository;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class MarketController
 * @package App\Http\Controllers\API
 */

class MarketAPIController extends Controller
{
    /** @var  MarketRepository */
    private $marketRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;


    public function __construct(MarketRepository $marketRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo,
    CategoryRepository $categoryRepository)
    {
        parent::__construct();
        $this->marketRepository = $marketRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->categoryRepository = $categoryRepository;

    }

    /**
     * Display a listing of the Market.
     * GET|HEAD /markets
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $lat = $request->myLat;
        $lon = $request->myLon;

        $markets = $this->marketRepository->pushCriteria(new RequestCriteria($request));
        $markets = $markets->pushCriteria(new LimitOffsetCriteria($request));

        if($lat && $lon) {
            $cities = City::all();
            foreach ($cities as $city){
                $userCity = City::select("cities.*"
                    ,DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
                    * cos(radians(cities.center_latitude)) 
                    * cos(radians(cities.center_longitude) - radians(" . $lon . ")) 
                    + sin(radians(" .$lat. ")) 
                    * sin(radians(cities.center_latitude))) AS distance"))
                    ->having('distance', '<', $city->radius)
                    ->first();
            }

            $cityLatitude = $userCity->center_latitude ?? 0;
            $cityLongitude = $userCity->center_longitude ?? 0;
            $radius = $userCity->radius ?? 0;

            $markets = $markets->pushCriteria(new LocationCriteria($request, $cityLatitude, $cityLongitude, $radius));

            if (empty($request->popular)) {
                $markets = $markets->pushCriteria(new NearCriteria($request, $cityLatitude, $cityLongitude));
            }
        }

        if($request->search ){
            $search = $request->search;
            $markets = $this->marketRepository->where('name', 'like', '%'. $search .'%');

        }

        if ($request->sector_id) {
            $field_id = $request->sector_id;
            $markets = $markets->where(function ($query) use ($field_id){
                $query->whereHas('fields', function ($query) use ($field_id) {
                    $query->where('field_id', $field_id);
                })->orWhere('primary_sector_id', $field_id);
            });
        }

        if ($request->category_id) {
            $markets = $markets
                ->whereHas('market_categories', function ($query) use ($request) {
                $query->where('id', $request->category_id);
            });
        }

        $markets = $markets->get();

        return $this->sendResponse($markets->toArray(), 'Markets retrieved successfully');
    }

    /**
     * Display the specified Market.
     * GET|HEAD /markets/{id}
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        /** @var Market $market */
        if (!empty($this->marketRepository)) {
            try{
                $this->marketRepository->pushCriteria(new RequestCriteria($request));
                $this->marketRepository->pushCriteria(new LimitOffsetCriteria($request));
//                if ($request->has(['myLon', 'myLat'])) {
//                    $cities = City::all();
//
//                    foreach ($cities as $city){
//
//                            $userCity = City::select("cities.*"
//                                ,DB::raw("6371 * acos(cos(radians(" . $request->get('myLat') . "))
//                        * cos(radians(cities.center_latitude))
//                        * cos(radians(cities.center_longitude) - radians(" . $request->get('myLon') . "))
//                        + sin(radians(" .$request->get('myLat'). "))
//                        * sin(radians(cities.center_latitude))) AS distance"))
//                                ->having('distance', '<', $city->radius)
//                                ->first();
//                    }
//
//                    $cityLatitude = $userCity->center_latitude ?? 0;
//                    $cityLongitude = $userCity->center_longitude ?? 0;
//
//                    $this->marketRepository->pushCriteria(new NearCriteria($request, $cityLatitude, $cityLongitude));
//                }
            } catch (RepositoryException $e) {
                return $this->sendError($e->getMessage());
            }
            $market = $this->marketRepository->findWithoutFail($id);
        }

        if (empty($market)) {
            return $this->sendError('Market not found');
        }

        return $this->sendResponse($market->toArray(), 'Market retrieved successfully');
    }

    /**
     * Store a newly created Market in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $input = $request->all();
        if (auth()->user()->hasRole('manager')){
            $input['users'] = [auth()->id()];
        }
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->marketRepository->model());
        try {
            $market = $this->marketRepository->create($input);
            $market->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($market, 'image');
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($market->toArray(),__('lang.saved_successfully', ['operator' => __('lang.market')]));
    }

    /**
     * Update the specified Market in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $market = $this->marketRepository->findWithoutFail($id);

        if (empty($market)) {
            return $this->sendError('Market not found');
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->marketRepository->model());
        try {
            $market = $this->marketRepository->update($input, $id);
            $input['users'] = isset($input['users']) ? $input['users'] : [];
            $input['drivers'] = isset($input['drivers']) ? $input['drivers'] : [];
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($market, 'image');
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $market->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($market->toArray(),__('lang.updated_successfully', ['operator' => __('lang.market')]));
    }

    /**
     * Remove the specified Market from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $market = $this->marketRepository->findWithoutFail($id);

        if (empty($market)) {
            return $this->sendError('Market not found');
        }

        $market = $this->marketRepository->delete($id);

        return $this->sendResponse($market,__('lang.deleted_successfully', ['operator' => __('lang.market')]));
    }
}
