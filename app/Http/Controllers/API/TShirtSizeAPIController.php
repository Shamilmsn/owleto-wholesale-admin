<?php

namespace App\Http\Controllers\API;


use App\Criteria\Coupons\ValidCriteria;
use App\Models\Coupon;
use App\Repositories\CityRepository;
use App\Repositories\CouponRepository;
use App\Repositories\TShirtSizeRepository;
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

class TShirtSizeAPIController extends Controller
{
    /** @var  TShirtSizeRepository */
    private $tShirtSizeRepository;

    public function __construct(TShirtSizeRepository $tShirtSizeRepository)
    {
        $this->tShirtSizeRepository = $tShirtSizeRepository;
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
            $this->tShirtSizeRepository->pushCriteria(new RequestCriteria($request));
            $this->tShirtSizeRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        $tshirts = $this->tShirtSizeRepository->all();

        return $this->sendResponse($tshirts->toArray(), 't shirt sizes retrieved successfully');
    }

}
