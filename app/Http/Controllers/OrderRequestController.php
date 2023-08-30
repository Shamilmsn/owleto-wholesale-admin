<?php

namespace App\Http\Controllers;

use App\Criteria\Products\ProductsOfMarketCriteria;
use App\Criteria\Products\ProductsOfUserCriteria;
use App\DataTables\OrderRequestCartDataTable;
use App\DataTables\OrderRequestDataTable;
use App\DataTables\OrderStatusDataTable;
use App\DataTables\TemporaryOrderRequestDatatable;
use App\Http\Requests;
use App\Http\Requests\CreateOrderStatusRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Product;
use App\Models\TemporaryOrderRequest;
use App\Repositories\OrderRequestRepository;
use App\Repositories\OrderStatusRepository;
use App\Repositories\CustomFieldRepository;

use App\Repositories\ProductRepository;
use App\Repositories\TemporaryOrderRequestRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Laracasts\Flash\Flash;
use Prettus\Validator\Exceptions\ValidatorException;

class OrderRequestController extends Controller
{
    /** @var  orderRequestRepository */
    private $orderRequestRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /** @var  ProductRepository */
    private $productRepository;

    /** @var  TemporaryOrderRequestRepository */
    private $temporaryOrderRequestRepository;

    /** @var  OrderStatusRepository */
    private $orderStatusRepository;


    public function __construct(orderRequestRepository $orderRequestRepo, CustomFieldRepository $customFieldRepo , ProductRepository $productRepo,
    TemporaryOrderRequestRepository $temporaryOrderRequestRepository, OrderStatusRepository $orderStatusRepository)
    {
        parent::__construct();
        $this->orderRequestRepository = $orderRequestRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->productRepository = $productRepo;
        $this->temporaryOrderRequestRepository = $temporaryOrderRequestRepository;
        $this->orderStatusRepository = $orderStatusRepository;

    }

    /**
     * Display a listing of the OrderStatus.
     *
     * @param OrderRequestDataTable $orderRequestDataTable
     * @return Response
     */
    public function index(OrderRequestDataTable $orderRequestDataTable)
    {
        return $orderRequestDataTable->render('order_requests.index');
    }


    /**
     * Display the specified OrderStatus.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show(TemporaryOrderRequestDatatable $temporaryOrderRequestDatatable, $id)
    {
        $orderRequest = $this->orderRequestRepository->with('market', 'temporaryOrderRequest')->findWithoutFail($id);
        $tempOrderRequest = $this->temporaryOrderRequestRepository->where('order_request_id',$id)->first();
        $tempOrderRequestCount = TemporaryOrderRequest::where('order_request_id',$id)->count();
        $statuses = TemporaryOrderRequest::$statuses;

        $imageSrc = null;
        if($orderRequest->image){
            $imageSrc = url('storage/order-requests/images/'.$orderRequest->image);

        }

        if (empty($orderRequest)) {
            Flash::error('Order Status not found');

            return redirect(route('orderRequests.index'));
        }

        $this->productRepository->pushCriteria(new ProductsOfMarketCriteria($orderRequest->market_id));

        $products = Product::all();

        return $temporaryOrderRequestDatatable->with('id', $id)
            ->render('order_requests.show', compact('orderRequest', 'products', 'imageSrc','tempOrderRequest','statuses','tempOrderRequestCount'));

    }
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $orderRequest = $this->orderRequestRepository->findWithoutFail($input['id']);
        try {
            if ($orderRequest->hasMedia($input['collection'])) {
                $orderRequest->getFirstMedia($input['collection'],['uuid'=>$input['uuid']])->delete();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

}
