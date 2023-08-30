<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\User;
use App\Notifications\NewOrderRequest;
use App\Repositories\OrderRequestRepository;
use App\Repositories\UploadRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class OrderRequestAPIController extends Controller
{
    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    /**
     * @var OrderRequestRepository
     */
    private $orderRequestRepository;

    public function __construct(OrderRequestRepository $orderRequestRepo, UploadRepository $uploadRepo)
    {
        parent::__construct();
        $this->orderRequestRepository = $orderRequestRepo;
        $this->uploadRepository = $uploadRepo;

    }

    public function index(Request $request)
    {

        try {
            $this->orderRequestRepository->pushCriteria(new RequestCriteria($request));
            $this->orderRequestRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        $orderRequests = $this->orderRequestRepository->with(['temporaryOrderRequest', 'market', 'deliveryType'])
            ->where('user_id', $request->user_id);

        if($request->market_id && $request->sector_id){
            $orderRequests = $orderRequests->where('market_id', $request->market_id)
                ->where('sector_id', $request->sector_id);
        }

        if($request->status){
            $orderRequests = $orderRequests->where('status', $request->status);
        }

        $orderRequests = $orderRequests->orderBy('id','desc')->get();


        return $this->sendResponse($orderRequests->toArray(), 'Orders Request retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $input['status'] = OrderRequest::STATUS_NEW;
        $input['image'] = null;

        if ($request->file('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $request->file('image')->storeAs('public/order-requests/images', $filename);

            $input['image'] = $filename;
        }

        $orderRequest = $this->orderRequestRepository->create($input);

        $customer = User::where('id', $request->user_id)->first();
        $market = Market::with('users')->where('id', $request->market_id)->first();

        try {
            if (count($market->users) > 0) {
                foreach ($market->users as $user) {
                    $userFcmToken[] = $user->device_token;
                    $attributes['title'] = 'Manual Order Request';
                    $attributes['redirection_type'] = Order::MANUAL_ORDER_REDIRECTION_TYPE;
                    $attributes['message'] = 'Manual order has been received from ' . $customer->name;
                    $attributes['data'] = null;
                    $attributes['redirection_id'] = $orderRequest->id;

                    Notification::route('fcm', $userFcmToken)
                        ->notify(new NewOrderRequest($attributes));
                }
            }

        }catch (\Exception $e) {

        }

        return $this->sendResponse($orderRequest->toArray(),'Order placed successfully');
    }
}
