<?php

namespace App\Http\Controllers\API;


use App\Criteria\Coupons\ValidCriteria;
use App\Models\Coupon;
use App\Models\OrderCoupon;
use App\Repositories\CouponRepository;
use Carbon\Carbon;
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

class CouponAPIController extends Controller
{
    /** @var  CouponRepository */
    private $couponRepository;

    public function __construct(CouponRepository $couponRepo)
    {
        $this->couponRepository = $couponRepo;
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
            $this->couponRepository->pushCriteria(new RequestCriteria($request));
            $this->couponRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->couponRepository->pushCriteria(new ValidCriteria());
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        $coupons = $this->couponRepository->all();

        return $this->sendResponse($coupons->toArray(), 'Coupons retrieved successfully');
    }

    /**
     * Display the specified Coupon.
     * GET|HEAD /coupons/{id}
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        /** @var Coupon $coupon */
        if (!empty($this->couponRepository)) {
            $coupon = $this->couponRepository->findWithoutFail($id);
        }

        if (empty($coupon)) {
            return $this->sendError('Coupon not found');
        }

        return $this->sendResponse($coupon->toArray(), 'Coupon retrieved successfully');
    }
    public  function validCoupon (Request $request) {

        $coupon = $this->couponRepository->where('code', $request->coupon_code)->first();

        if(! $coupon) {
            return $this->sendError('Coupon not found');
        }
            $isCouponValid = Coupon::where('enabled','1')->where('expires_at','>',Carbon::now())
                ->where('id', $coupon->id)->first();
        if(! $isCouponValid) {
            return $this->sendError('Coupon is not enabled or Coupon Expired', 422);
        }

        $latestOrderCoupon = OrderCoupon::where('user_id', $request->user_id)
            ->where('coupon_id', $coupon->id)
            ->orderBy('id','desc')
            ->first();

        $totalCouponUsedCount = OrderCoupon::where('coupon_id', $coupon->id)->count();

        if ($totalCouponUsedCount > $coupon->total_number_of_coupon) {

            return $this->sendError('Coupon limit Expired', 422);
        }

        if ($request->order_amount < $coupon->minimum_order_value) {

            return $this->sendError('Your order amount should be greater than '.$coupon->minimum_order_value.
                ' to apply this coupon', 422);
        }
        if ($latestOrderCoupon) {

            if ($latestOrderCoupon->number_of_usage > $coupon->use_limit_per_person ) {
                return $this->sendError('Your Coupon limit Expired', 422);
            }
        }


        return $this->sendResponse($coupon->toArray(), 'Coupons retrieved successfully');
    }
}
