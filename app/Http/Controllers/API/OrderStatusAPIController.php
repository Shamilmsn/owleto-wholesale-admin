<?php

namespace App\Http\Controllers\API;


use App\Models\Field;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Repositories\OrderStatusRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Illuminate\Support\Facades\Response;
use Prettus\Repository\Exceptions\RepositoryException;
use Flash;

/**
 * Class OrderStatusController
 * @package App\Http\Controllers\API
 */

class OrderStatusAPIController extends Controller
{
    /** @var  OrderStatusRepository */
    private $orderStatusRepository;

    public function __construct(OrderStatusRepository $orderStatusRepo)
    {
        $this->orderStatusRepository = $orderStatusRepo;
    }

    /**
     * Display a listing of the OrderStatus.
     * GET|HEAD /orderStatuses
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $this->orderStatusRepository->pushCriteria(new RequestCriteria($request));
            $this->orderStatusRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        $orderStatuses = OrderStatus::query();

        $order = Order::findOrFail($request->order_id);

        if($order->order_status_id == OrderStatus::STATUS_RECEIVED){
            $orderStatuses = $orderStatuses->whereIn('id', [OrderStatus::STATUS_RECEIVED]);
        }

        if($order->order_status_id == OrderStatus::STATUS_PREPARING){
            $orderStatuses = $orderStatuses->whereIn('id', [OrderStatus::STATUS_RECEIVED,
                OrderStatus::STATUS_PREPARING]);
        }

        if($order->order_status_id == OrderStatus::STATUS_PREPARING){
            $orderStatuses = $orderStatuses->whereIn('id', [OrderStatus::STATUS_RECEIVED,
                OrderStatus::STATUS_PREPARING,
                OrderStatus::STATUS_READY]);
        }

        if($order->order_status_id == OrderStatus::STATUS_CANCELED){
            $orderStatuses = $orderStatuses->whereIn('id', [OrderStatus::STATUS_RECEIVED, OrderStatus::STATUS_CANCELED]);
        }

        if($order->order_status_id == OrderStatus::STATUS_DRIVER_ASSIGNED){

            $orderStatuses = $orderStatuses->whereIn('id', [OrderStatus::STATUS_RECEIVED,
                OrderStatus::STATUS_READY]);

            if($order->sector_id = Field::RESTAURANT || $order->sector_id == Field::HOME_COOKED_FOOD){
                $orderStatuses = $orderStatuses->whereIn('id', [OrderStatus::STATUS_RECEIVED,
                    OrderStatus::STATUS_PREPARING, OrderStatus::STATUS_READY]);
            }
        }

        if($order->order_status_id == OrderStatus::STATUS_DELIVERED){

            $orderStatuses = $orderStatuses->whereIn('id', [OrderStatus::STATUS_RECEIVED, OrderStatus::STATUS_READY, OrderStatus::STATUS_ON_THE_WAY,
                OrderStatus::STATUS_DELIVERED]);

            if($order->sector_id = Field::RESTAURANT || $order->sector_id == Field::HOME_COOKED_FOOD){
                $orderStatuses = $orderStatuses->whereIn('id', [OrderStatus::STATUS_RECEIVED,
                    OrderStatus::STATUS_PREPARING, OrderStatus::STATUS_READY, OrderStatus::STATUS_ON_THE_WAY,
                    OrderStatus::STATUS_DELIVERED]);
            }

            if($order->sector_id == Field::TAKEAWAY){
                $orderStatuses = $orderStatuses->whereIn('id', [OrderStatus::STATUS_RECEIVED, OrderStatus::STATUS_DELIVERED]);
            }
        }

        if($order->order_status_id == OrderStatus::STATUS_ON_THE_WAY){
            $orderStatuses = $orderStatuses->whereIn('id', [OrderStatus::STATUS_RECEIVED,
                OrderStatus::STATUS_PREPARING, OrderStatus::STATUS_READY, OrderStatus::STATUS_ON_THE_WAY]);
        }


        $orderStatuses = $orderStatuses->get();

        return $this->sendResponse($orderStatuses->toArray(), 'Order Statuses retrieved successfully');
    }

    /**
     * Display the specified OrderStatus.
     * GET|HEAD /orderStatuses/{id}
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        /** @var OrderStatus $orderStatus */
        if (!empty($this->orderStatusRepository)) {
            $orderStatus = $this->orderStatusRepository->findWithoutFail($id);
        }

        if (empty($orderStatus)) {
            return $this->sendError('Order Status not found');
        }

        return $this->sendResponse($orderStatus->toArray(), 'Order Status retrieved successfully');
    }
}
