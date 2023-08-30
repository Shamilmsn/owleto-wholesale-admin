<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\TemporaryOrderRequest;
use App\Models\User;
use App\Notifications\OrderRequestPushNotification;
use App\Repositories\OrderRequestRepository;
use App\Repositories\TemporaryOrderRequestRepository;
use App\Repositories\UploadRepository;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Laracasts\Flash\Flash;
use Prettus\Validator\Exceptions\ValidatorException;

class TemporaryOrderRequestController extends Controller
{

    /**
     * @var TemporaryOrderRequestRepository
     */
    private $temporaryOrderRequestRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;
    /**
     * @var OrderRequestRepository
     */
    private $orderRequestRepository;


    public function __construct(TemporaryOrderRequestRepository $temporaryOrderRequestRepository , UploadRepository $uploadRepo,
                                OrderRequestRepository  $orderRequestRepository)
    {
        parent::__construct();
        $this->temporaryOrderRequestRepository = $temporaryOrderRequestRepository;
        $this->uploadRepository = $uploadRepo;
        $this->orderRequestRepository = $orderRequestRepository;
    }

    public function store(Request $request)
    {
        if(!isset($request->image)) {
            Flash::error('Upload the bill');
            return Redirect::back();
        }

        $input = $request->all();

        try {
            $orderRequest = OrderRequest::findOrFail($request->order_request_id);
            $status = OrderRequest::STATUS_NOTIFICATION_SEND;
            $input['status'] = $status;

            $user = User::where('id',$orderRequest->user_id)->first();
            $orderRequest->status = $status;
            $orderRequest->save();

            $input['distance'] = $orderRequest->distance;


            if ($request->file('image')) {
                $billImage = $request->file('image');
                $billImageFileName = time() . '.' . $billImage->getClientOriginalExtension();
                $billImage->storeAs('public/order-requests/bill-images/', $billImageFileName);
                $input['bill_image'] = $billImageFileName;
            }

            $temporaryOrderRequest = $this->temporaryOrderRequestRepository->create($input);

            $orderRequestData = $this->orderRequestRepository->with('temporaryOrderRequest')->findWithoutFail($request->order_request_id);
            $totalAmount = $orderRequest->delivery_fee + $temporaryOrderRequest->net_amount;
            $userFcmToken = $user->device_token;
            $attributes['title'] = 'Owleto manual order bill';
            $attributes['redirection_type'] = Order::TEMPORARY_ORDER_REDIRECTION_TYPE;

            $attributes['message'] = 'Hi, Thank you for choosing Owleto.Your order has been updated with bill amount '.$totalAmount.' Please refer the bill attached here.';
            $url = url($temporaryOrderRequest->getFirstMediaUrl('image', 'thumb'));
            $attributes['image'] = $url;
            $attributes['data'] = $orderRequestData->toArray();

            try {
                \Illuminate\Support\Facades\Notification::route('fcm', $userFcmToken)
                    ->notify(new OrderRequestPushNotification($attributes));

            } catch (\Exception $exception) {

            }

        }
        catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success('Order Request Saved Successfully');
        return Redirect::back();
    }

    public function destroy($id)
    {
        $tempOrderRequest = $this->temporaryOrderRequestRepository->findWithoutFail($id);

        if (empty($tempOrderRequest)) {
            Flash::error('Order Request not found');

            return redirect(route('orderRequests.index'));
        }
        $this->temporaryOrderRequestRepository->delete($id);

        Flash::success(__('lang.deleted_successfully',['operator' => __('lang.orderRequest')]));

        return Redirect::back();
    }

    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $tempOrderRequest = TemporaryOrderRequest::findOrFail($input['id']);
        try {
            if ($tempOrderRequest->hasMedia($input['collection'])) {
                $tempOrderRequest->getFirstMedia($input['collection'],['uuid'=>$input['uuid']])->delete();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
