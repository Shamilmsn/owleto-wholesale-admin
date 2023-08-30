<?php

namespace App\Http\Controllers\API;


use App\Criteria\Circles\CityCriteria;
use App\Criteria\Coupons\ValidCriteria;
use App\Criteria\Slots\StatusCriteria;
use App\Models\Coupon;
use App\Repositories\CircleRepository;
use App\Repositories\CityRepository;
use App\Repositories\CouponRepository;
use App\Repositories\SlotRepository;
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

class SlotAPIController extends Controller
{
    /** @var  SlotRepository */
    private $slotRepository;

    public function __construct(SlotRepository $slotRepository)
    {
        $this->slotRepository = $slotRepository;
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
            $this->slotRepository->pushCriteria(new RequestCriteria($request));
            $this->slotRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->slotRepository->pushCriteria(new StatusCriteria());
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        $coupons = $this->slotRepository->all();

        return $this->sendResponse($coupons->toArray(), 'Slots retrieved successfully');
    }

}
