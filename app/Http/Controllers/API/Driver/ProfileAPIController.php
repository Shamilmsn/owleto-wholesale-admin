<?php
/**
 * File name: UserAPIController.php
 * Last modified: 2020.10.29 at 17:03:54
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API\Driver;

use App\Criteria\DriverTransactions\DriverTransactionOfUsers;
use App\Events\UserRoleChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Driver;
use App\Models\DriverBankDetail;
use App\Models\DriverDocument;
use App\Models\DriverPersonalDetail;
use App\Models\User;
use App\Repositories\CustomFieldRepository;
use App\Repositories\DriverPayoutRequestRepository;
use App\Repositories\DriverRepository;
use App\Repositories\DriverTransactionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class ProfileAPIController extends Controller
{
    /**
     * @var DriverRepository
     */
    private $driverRepository;

    /**
     * @var DriverPayoutRequestRepository
     */

    private $driverPayoutRequestRepository;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DriverRepository $driverRepository, DriverPayoutRequestRepository $driverPayoutRequestRepository)
    {
        $this->driverRepository = $driverRepository;
        $this->driverPayoutRequestRepository = $driverPayoutRequestRepository;
    }

    public function index(Request $request)
    {
        try{
            $this->driverRepository->pushCriteria(new RequestCriteria($request));
            $this->driverRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
//            return $this->sendError($e->getMessage());
        }
        $attributes = $this->driverRepository->where('user_id', $request->user_id)->first();

        return $this->sendResponse($attributes, 'Driver profile retrieved successfully');
    }

    public function payOutRequest(Request $request)
    {
        $input = $request->all();

        $appSetting = AppSetting::where('key','min_payout_amount')->first();
        $driver = Driver::where('user_id', $input['user_id'])->first();

        if($driver->balance < $input['amount']){
            $errorMsg = 'Does not have sufficient balance';
            return $this->sendError($errorMsg,409);
        }

        if($input['amount'] < $appSetting->value){
            $errorMsg = 'Minimum request amount should be ' . $appSetting->value;
            return $this->sendError($errorMsg,409);
        }


        $driverPayoutRequest = $this->driverPayoutRequestRepository->create($input);

        return $this->sendResponse($driverPayoutRequest->toArray(), __('lang.saved_successfully',['operator' => __('lang.payoutRequest')]));

    }

    public function updateLocalityDetails(Request $request)
    {
        $userId = $request->user_id;

        DB::beginTransaction();

        $driver = Driver::where('user_id', $userId)->first();

        if(!$driver){
            $driver = new Driver();
        }
        $driver->city_id = $request->city_id;
        $driver->user_id = $userId;
        $driver->circle_id = $request->circle_id;
        $driver->vehicle_id = $request->vehicle_id;
        $driver->save();

        $user = User::find($userId);
        $user->driver_signup_status = User::DRIVER_SIGNUP_COMPLETED_LOCALITY_AND_VEHICLE_DETAILS;
        $user->city_id = $request->city_id;
        $user->area_id = $request->circle_id;
        $user->save();

        $user->syncRoles('driver');

        DB::commit();

        return $this->sendResponse($user,'Locality and vehicle added');

    }

    public function updatePersonalDetails(Request $request)
    {

        try{
            $userId = $request->user_id;

            $validator = Validator::make($request->all(), [
                'email' => 'unique:users,email,'.$userId,
            ],
            [
                'email.unique' => 'The email has already been taken.',

            ]);

            if ($validator->fails()) {

                return $this->error('Invalid data', $validator->errors(), 422);
            }

            DB::beginTransaction();

            $driverPersonalDetail = DriverPersonalDetail::where('user_id', $userId)->first();

            if(!$driverPersonalDetail){
                $driverPersonalDetail = new DriverPersonalDetail();
            }

            $driverPersonalDetail->user_id = $userId;
            $driverPersonalDetail->name = $request->name;
            $driverPersonalDetail->email = $request->email;
            $driverPersonalDetail->date_of_birth = Carbon::parse($request->date_of_birth);
            $driverPersonalDetail->gender = $request->gender;
            $driverPersonalDetail->address_line_1 = $request->address_line_1;
            $driverPersonalDetail->address_line_2 = $request->address_line_2;
            $driverPersonalDetail->city = $request->city;
            $driverPersonalDetail->state = $request->state;
            $driverPersonalDetail->pincode = $request->pincode;
            $driverPersonalDetail->t_shirt_size_id = $request->t_shirt_size_id;
            $driverPersonalDetail->save();

            $user = User::find($userId);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->driver_signup_status = User::DRIVER_SIGNUP_COMPLETED_PERSONAL_INFO;
            $user->save();

            DB::commit();

            return $this->sendResponse($user,'Personal details added');

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage(), 422);
        }

    }

    public function updateBankDetails(Request $request)
    {
        $userId = $request->user_id;

        $driverBankDetail = DriverBankDetail::where('user_id', $userId)->first();

        if(!$driverBankDetail){
            $driverBankDetail = new DriverBankDetail();
        }

        $driverBankDetail->user_id = $userId;
        $driverBankDetail->bank_name = $request->bank_name;
        $driverBankDetail->account_number = $request->account_number;
        $driverBankDetail->ifsc_code = $request->ifsc_code;
        $driverBankDetail->account_holder_name = $request->account_holder_name;
        $driverBankDetail->save();

        $driverDocument = DriverDocument::where('user_id', $userId)->first();

        if(!$driverDocument){
            $driverDocument = new DriverDocument();
        }

        $driverDocument->user_id = $userId;
        $driverDocument->pancard_number = $request->pancard_number;
        $driverDocument->license_number = $request->license_number;

        if ($request->file('pancard_file')) {
            $pancardImage = $request->file('pancard_file');
            $pancardImageFileName = time() . '.' . $pancardImage->getClientOriginalExtension();
            $pancardImage->storeAs('public/pancards/images', $pancardImageFileName);

            $driverDocument->pancard_file = $pancardImageFileName;
        }

        if ($request->file('license_file')) {
            $license = $request->file('license_file');
            $licenseFile = time() . '.' . $license->getClientOriginalExtension();
            $license->storeAs('public/licenses/images', $licenseFile);

            $driverDocument->license_file = $licenseFile;
        }

        $driverDocument->save();

        $user = User::find($userId);
        $user->driver_signup_status = User::DRIVER_SIGNUP_COMPLETED_BANK_INFO;
        $user->save();

        return $this->sendResponse($user,'Personal details added');

    }

    public function updateProfileImage(Request $request)
    {
        $userId = $request->user_id;

        $driverPersonalDetail = DriverPersonalDetail::where('user_id', $userId)->first();
        $user = User::find($userId);

        if(!$driverPersonalDetail){
            $driverPersonalDetail = new DriverPersonalDetail();
        }

        if ($request->file('file')) {
            $file = $request->file('file');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/profile-images', $fileName);

            $driverPersonalDetail->profile_image = $fileName;
            $user->profile_image = $fileName;
        }
        else {
            $driverPersonalDetail->profile_image = null;
            $user->profile_image = null;
        }

        $driverPersonalDetail->user_id = $userId;
        $driverPersonalDetail->save();

        $user->driver_signup_status = User::DRIVER_SIGNUP_COMPLETED_PROFILE_UPLOAD;
        $user->save();

        return $this->sendResponse($user,'Profile image uploaded successfully');
    }

    public function changeProfileImage(Request $request)
    {
        $userId = $request->user_id;

        $driverPersonalDetail = DriverPersonalDetail::where('user_id', $userId)->first();
        $user = User::find($userId);

        if(!$driverPersonalDetail){
            $driverPersonalDetail = new DriverPersonalDetail();
        }

        if ($request->file('file')) {
            $file = $request->file('file');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/profile-images', $fileName);

            $driverPersonalDetail->profile_image = $fileName;
            $user->profile_image = $fileName;
        }
        else {
            $driverPersonalDetail->profile_image = null;
            $user->profile_image = null;
        }

        $driverPersonalDetail->save();
        $user->save();

        return $this->sendResponse($user,'Profile image updated successfully');
    }

}
