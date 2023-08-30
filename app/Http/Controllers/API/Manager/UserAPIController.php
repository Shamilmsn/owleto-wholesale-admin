<?php
/**
 * File name: UserAPIController.php
 * Last modified: 2020.10.29 at 17:03:55
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API\Manager;

use App\Criteria\Earnings\EarningOfUserCriteria;
use App\Criteria\Orders\OrdersOfUserCriteria;
use App\Criteria\Users\DriversOfMarketCriteria;
use App\Events\UserRoleChangedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMerchantProfileRequest;
use App\Models\Earning;
use App\Models\Market;
use App\Models\MarketsPayout;
use App\Models\MarketTransaction;
use App\Models\User;
use App\Models\UserDevice;
use App\Repositories\CustomFieldRepository;
use App\Repositories\EarningRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class UserAPIController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    private $uploadRepository;
    private $roleRepository;
    private $customFieldRepository;

    /**
     * @var EarningRepository
     */
    private $earningRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository, UploadRepository $uploadRepository,
                                RoleRepository $roleRepository, CustomFieldRepository $customFieldRepo, EarningRepository $earningRepository)
    {
        $this->userRepository = $userRepository;
        $this->uploadRepository = $uploadRepository;
        $this->roleRepository = $roleRepository;
        $this->customFieldRepository = $customFieldRepo;
        $this->earningRepository = $earningRepository;
    }

    function login(Request $request)
    {
        try {

            $this->validate($request, [
                'phone' => 'required',
            ]);

            $phone = $request->input('phone');
            $device_token = $request->input('device_token');
            $device_type = $request->input('device_type');

            if (is_numeric($phone)) {
                $user = User::with('markets')
                    ->where('phone', $phone)
                    ->orWhere('phone', '91' . $phone)->first();

                if (!$user) {
                    return $this->sendError("No user found", 403);
                }

                if (!$user->hasRole('vendor_owner')) {

                    return $this->sendError(
                        "Number already registered, Please login using Different mobile number",
                        403
                    );
                }

                if (count($user->markets) <= 0) {
                    return $this->sendError(
                        "No markets added",
                        403
                    );
                }
            }

            User::query()
                ->where('device_token', $device_token)
                ->where('id', '!=', $user->id)
                ->update(['device_token' => null]);


            $user->device_token = $device_token;
            $user->device_type = $device_type;
            $user->save();

            $user->syncRoles(['vendor_owner']);

            $userDevice = UserDevice::where('user_id', $user->id)->first();

            if (!$userDevice) {
                $userDevice = new UserDevice();
            }

            $userDevice->user_id = $user->id;
            $userDevice->device_type = $device_type;
            $userDevice->fcm_token = $device_token;
            $userDevice->save();

            Auth::login($user);

            $token = $user->createToken('accessToken')->plainTextToken;

            $user->api_token = $token;
            $user->save();

            return $this->sendResponse(['user' => $user, 'token' => $token], 'You are successfully logged in');

        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }

    }

    /**
     * Update the specified User in storage.
     *
     * @param int $id
     * @param Request $request
     *
     */
    public function update($id, Request $request)
    {
        $user = $this->userRepository->findWithoutFail($id);

        if (empty($user)) {
            return $this->sendResponse([
                'error' => true,
                'code' => 404,
            ], 'User not found');
        }
        $input = $request->except(['password', 'api_token']);
        try {
            if ($request->has('device_token')) {
                $user = $this->userRepository->update($request->only('device_token'), $id);
            } else {
                $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->userRepository->model());
                $user = $this->userRepository->update($input, $id);

                foreach (getCustomFieldsValues($customFields, $request) as $value) {
                    $user->customFieldsValues()
                        ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
                }
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage(), 401);
        }

        return $this->sendResponse($user, __('lang.updated_successfully', ['operator' => __('lang.user')]));
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return
     */
    function register(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|unique:users|email',
                'password' => 'required',
            ]);
            $user = new User;
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->device_token = $request->input('device_token', '');
            $user->password = Hash::make($request->input('password'));
            $user->api_token = str_random(60);
            $user->save();

            $defaultRoles = $this->roleRepository->findByField('default', '1');
            $defaultRoles = $defaultRoles->pluck('name')->toArray();
            $user->assignRole($defaultRoles);

            event(new UserRoleChangedEvent($user));
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }


        return $this->sendResponse($user, 'User retrieved successfully');
    }

    function logout(Request $request)
    {
        $user = $this->userRepository->findByField('api_token', $request->input('api_token'))->first();
        if (!$user) {
            return $this->sendError('User not found', 401);
        }
        try {
            auth()->logout();
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 401);
        }
        return $this->sendResponse($user['name'], 'User logout successfully');

    }

    function user(Request $request)
    {
        $user = $this->userRepository->findByField('api_token', $request->input('api_token'))->first();

        if (!$user) {
            return $this->sendError('User not found', 401);
        }

        return $this->sendResponse($user, 'User retrieved successfully');
    }

    function settings(Request $request)
    {
        $settings = setting()->all();
        $settings = array_intersect_key($settings,
            [
                'default_tax' => '',
                'default_currency' => '',
                'default_currency_decimal_digits' => '',
                'app_name' => '',
                'currency_right' => '',
                'enable_paypal' => '',
                'enable_stripe' => '',
                'enable_razorpay' => '',
                'main_color' => '',
                'main_dark_color' => '',
                'second_color' => '',
                'second_dark_color' => '',
                'accent_color' => '',
                'accent_dark_color' => '',
                'scaffold_dark_color' => '',
                'scaffold_color' => '',
                'google_maps_key' => '',
                'fcm_key' => '',
                'mobile_language' => '',
                'app_version' => '',
                'enable_version' => '',
                'distance_unit' => '',
            ]
        );

        if (!$settings) {
            return $this->sendError('Settings not found', 401);
        }

        return $this->sendResponse($settings, 'Settings retrieved successfully');
    }

    function sendResetLinkEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        $response = Password::broker()->sendResetLink(
            $request->only('email')
        );

        if ($response == Password::RESET_LINK_SENT) {
            return $this->sendResponse(true, 'Reset link was sent successfully');
        } else {
            return $this->sendError([
                'error' => 'Reset link not sent',
                'code' => 401,
            ], 'Reset link not sent');
        }

    }

    /**
     * Display a listing of the Drivers.
     * GET|HEAD /markets
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function driversOfMarket($id, Request $request)
    {
        try {
            $this->userRepository->pushCriteria(new RequestCriteria($request));
            $this->userRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->userRepository->pushCriteria(new DriversOfMarketCriteria($id));
            $users = $this->userRepository->all();

        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($users->toArray(), 'Drivers retrieved successfully');
    }

    public function updateProfile(UpdateMerchantProfileRequest $request)
    {
        $user = User::find(Auth::id());
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return $this->sendResponse($user, 'profile updated successfully');
    }

    public function updateProfileImage(Request $request)
    {
        $user = User::find(Auth::id());

        if ($request->file('file')) {
            $file = $request->file('file');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/profile-images', $fileName);
            $user->profile_image = $fileName;
        } else {
            $user->profile_image = null;
        }

        return $this->sendResponse($user, 'profile image successfully');

    }

    public function balanceAndEarnings(Request $request)
    {
        $userMarketIds = Market::whereHas('users', function ($query) {
            $query->where('user_id', auth()->id());
        })->pluck('id');

        $totalBalance = Earning::whereIn('market_id', $userMarketIds)->sum('market_balance');

        $totalEarning = MarketsPayout::whereIn('market_id', $userMarketIds)->sum('amount');
        $earning['description'] = 'total_earning';
        $earning['value'] = $totalEarning;
        $statistics[] = $earning;

        $ordersCount['description'] = "total_balance";
        $ordersCount['value'] = $totalBalance;
        $statistics[] = $ordersCount;

        return $this->sendResponse($statistics, 'Statistics retrieved successfully');
    }
}
