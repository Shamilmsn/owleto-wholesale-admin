<?php

namespace App\Http\Controllers\API;


use App\Criteria\Circles\CityCriteria;
use App\Criteria\Coupons\ValidCriteria;
use App\Models\Coupon;
use App\Repositories\CircleRepository;
use App\Repositories\CityRepository;
use App\Repositories\CouponRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Illuminate\Support\Facades\Response;
use Prettus\Repository\Exceptions\RepositoryException;
use Flash;

/**
 * Class CouponController
 * @package App\Http\Controllers\API
 */

class CircleAPIController extends Controller
{
    /** @var  CircleRepository */
    private $circleRepository;

    public function __construct(CircleRepository $circleRepository)
    {
        $this->circleRepository = $circleRepository;
    }

    /**
     * Display a listing of the Coupon.
     * GET|HEAD /coupons
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $this->circleRepository->pushCriteria(new RequestCriteria($request));
            $this->circleRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->circleRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->circleRepository->pushCriteria(new CityCriteria($request->city_id));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        $coupons = $this->circleRepository->all();

        return $this->sendResponse($coupons->toArray(), 'Areas retrieved successfully');
    }

}
