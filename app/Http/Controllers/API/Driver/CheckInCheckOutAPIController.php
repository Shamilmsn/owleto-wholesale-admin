<?php
/**
 * File name: UserAPIController.php
 * Last modified: 2020.10.29 at 17:03:54
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API\Driver;

use App\Criteria\Carts\CartsOfUsersCriteria;
use App\Criteria\DriverCheckInCheckOutHistory\DriverCheckInCheckOutOfUsersCriteria;
use App\Events\UserRoleChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use App\Models\User;
use App\Repositories\CustomFieldRepository;
use App\Repositories\DriverCheckInCheckOutHistoryRepository;
use App\Repositories\ProductOrderRequestOrderRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class CheckInCheckOutAPIController extends Controller
{

    /** @var  DriverCheckInCheckOutHistoryRepository */
    private $driverCheckInCheckOutHistoryRepository;

    /** @var  UserRepository */
    private $userRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DriverCheckInCheckOutHistoryRepository $driverCheckInCheckOutHistoryRepository, UserRepository $userRepository)
    {
        $this->driverCheckInCheckOutHistoryRepository = $driverCheckInCheckOutHistoryRepository;
        $this->userRepository = $userRepository;
    }

    public function index(Request $request)
    {
        try{
            $this->driverCheckInCheckOutHistoryRepository->pushCriteria(new RequestCriteria($request));
            $this->driverCheckInCheckOutHistoryRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->driverCheckInCheckOutHistoryRepository->pushCriteria(new DriverCheckInCheckOutOfUsersCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $drivers = $this->driverCheckInCheckOutHistoryRepository->all();

        return $this->sendResponse($drivers->toArray(), 'Driver checkin checkout details retrieved successfully');
    }

    public function checkInCheckOut(Request $request)
    {
        $request->validate([
            'user_id' => 'required|numeric',
            'type' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $input = $request->all();

        if($input['type'] == 'CHECKOUT'){

            $orders = Order::where('driver_id', $input['user_id'])
                ->whereDate('created_at', Carbon::today())
                ->where('order_status_id','!=', Order::STATUS_DELIVERED)
                ->get();
            if(count($orders) > 0){
                return $this->sendError('You cannot checkout now', 409);
            }

            $available = 0;
        }else{
            $available = 1;
        }

        $user = User::find($request->user_id);
        $user->driver_checkin_checkout_type = $request->type;
        $user->driver_checkin_checkout_latitude = $request->latitude;
        $user->driver_checkin_checkout_longitude = $request->longitude;
        $user->save();

        $driver = Driver::where('user_id', $user->id)->first();
        if($driver){
            if($driver->admin_approved){
                $driver->available = $available;
                $driver->save();
            }
        }

        $driverCheckInCheckOut = $this->driverCheckInCheckOutHistoryRepository->create($input);

        if($request->type == 'CHECKIN'){
            return $this->sendResponse($driverCheckInCheckOut->toArray(), __('lang.driverCheckinSuccess',['operator' => __('lang.driver')]));
        }
        else{
            return $this->sendResponse($driverCheckInCheckOut->toArray(), __('lang.driverCheckoutSuccess',['operator' => __('lang.driver')]));
        }

    }

}
