<?php

namespace App\Http\Controllers\API;

use App\Criteria\Earnings\EarningOfUserCriteria;
use App\Criteria\Markets\MarketsOfManagerCriteria;
use App\Criteria\Orders\OrdersOfUserCriteria;
use App\Criteria\Products\ProductsOfUserCriteria;
use App\Models\Earning;
use App\Models\Market;
use App\Models\MarketsPayout;
use App\Models\MarketTransaction;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Repositories\EarningRepository;
use App\Repositories\MarketRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class DashboardAPIController extends Controller
{
    /** @var  OrderRepository */
    private $orderRepository;

    /** @var  MarketRepository */
    private $marketRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var EarningRepository
     */
    private $earningRepository;

    public function __construct(OrderRepository $orderRepo, EarningRepository $earningRepository, MarketRepository $marketRepo, ProductRepository $productRepository)
    {
        parent::__construct();
        $this->orderRepository = $orderRepo;
        $this->marketRepository = $marketRepo;
        $this->productRepository = $productRepository;
        $this->earningRepository = $earningRepository;
    }

    /**
     * Display a listing of the Faq.
     * GET|HEAD /faqs
     * @param  int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function manager($id, Request $request)
    {
        $statistics = [];
        try{
            $userMarketIds = Market::whereHas('users', function ($query) {
                $query->where('id', Auth::id());
            })->pluck('id');

            $totalEarning = MarketsPayout::whereIn('market_id', $userMarketIds)->sum('amount');
            $earning['description'] = 'total_earning';
            $earning['value'] = $totalEarning;
            $statistics[] = $earning;

            $orderCount = Order::where(function ($query) use ($userMarketIds){
                    $query->whereIn('payment_method_id', [PaymentMethod::PAYMENT_METHOD_RAZORPAY, PaymentMethod::PAYMENT_METHOD_WALLET])
                        ->where('payment_status', 'SUCCESS')
                        ->whereIn('market_id', $userMarketIds);
                })
                ->orWhere(function ($query) use ($userMarketIds){
                    $query->whereIn('payment_method_id',[ PaymentMethod::PAYMENT_METHOD_COD,  PaymentMethod::PAYMENT_METHOD_WALLET])
                        ->whereIn('payment_status', ['PENDING', 'SUCCESS'])
                        ->whereIn('market_id', $userMarketIds);
                })->count();

            $ordersCount['description'] = "total_orders";
            $ordersCount['value'] = $orderCount;
            $statistics[] = $ordersCount;

            $productCount = Product::where('product_type', '!=', Product::VARIANT_BASE_PRODUCT)
                ->where('deliverable', 1)
                ->whereIn('market_id', $userMarketIds)
                ->where('is_approved', true)
                ->count();

            $productsCount['description'] = "total_products";
            $productsCount['value'] = $productCount;
            $statistics[] = $productsCount;


        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($statistics, 'Statistics retrieved successfully');
    }
}
