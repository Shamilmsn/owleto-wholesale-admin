<?php
/**
 * File name: UserAPIController.php
 * Last modified: 2020.10.29 at 17:03:54
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDevice;
use App\Repositories\CustomFieldRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Prettus\Validator\Exceptions\ValidatorException;


class UserAPIController extends Controller
{
    private $userRepository;
    private $uploadRepository;
    private $roleRepository;
    private $customFieldRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository, UploadRepository $uploadRepository, RoleRepository $roleRepository, CustomFieldRepository $customFieldRepo)
    {
        $this->userRepository = $userRepository;
        $this->uploadRepository = $uploadRepository;
        $this->roleRepository = $roleRepository;
        $this->customFieldRepository = $customFieldRepo;
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
            $user = $request->user();

            $this->validate($request, [
                'name' => 'required|max:255',
                'email' => 'nullable|unique:users,email,' . $user->id,
                'phone' => 'nullable|unique:users,phone,' . $user->id,

            ]);

            $user->name = $request->input('name');
            $user->email = $request->input('email');
//            $user->is_profile_registered = 1;
//            $user->is_profile_updated = 1;
//            $user->referral_user_id = $request->referral_user_id;
            $user->phone = $request->input('phone');
            $user->update();

//            $user = new User;
//            $user->name = $request->input('name');
//            $user->email = $request->input('email');
//            $user->device_token = $request->input('device_token', '');
//            $user->password = Hash::make($request->input('password'));
//            $user->api_token = str_random(60);
//            $user->save();

            $defaultRoles = $this->roleRepository->findByField('default', '1');
            $defaultRoles = $defaultRoles->pluck('name')->toArray();
            $user->assignRole($defaultRoles);

            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->userRepository->model());

            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $user->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }


            if (copy(public_path('images/avatar_default.png'), public_path('images/avatar_default_temp.png'))) {
                $user->addMedia(public_path('images/avatar_default_temp.png'))
                    ->withCustomProperties(['uuid' => bcrypt(str_random())])
                    ->toMediaCollection('avatar');
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }

        return $this->sendResponse(['user' => $user], 'You are successfully registered');

    }

    function user(Request $request)
    {
        $user = $this->userRepository->findByField('api_token', $request->input('api_token'))->first();

        if (!$user) {
            return $this->sendError('User not found', 401);
        }

        return $this->sendResponse($user, 'User retrieved successfully');
    }

    /**
     * Update the specified User in storage.
     *
     * @param int $id
     * @param Request $request
     *
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'unique:users,email,' . $id,
            'phone' => 'unique:users,phone,' . $id,
        ],
            [
                'email.unique' => 'The email has already been taken.',

            ]);

        if ($validator->fails()) {

            return $this->error('Invalid data', $validator->errors(), 422);
        }

        $user = $this->userRepository->findWithoutFail($id);

        if (empty($user)) {
            return $this->sendResponse([
                'error' => true,
                'code' => 404,
            ], 'User not found');
        }
        $input = $request->except(['password', 'api_token']);

        $user->name = $request->name;
        $user->address = $request->address;
        $user->email = $request->email;
        $user->about = $request->about;
        $user->phone = $request->phone;
        $user->save();

        try {
            if ($request->has('device_token')) {
                $user = $this->userRepository->update($request->only('device_token'), $id);
            } else {
                $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->userRepository->model());
                if (isset($input['avatar']) && $input['avatar']) {
                    $cacheUpload = $this->uploadRepository->getByUuid($input['avatar']);
                    $mediaItem = $cacheUpload->getMedia('avatar')->first();
                    if ($user->hasMedia('avatar')) {
                        $user->getFirstMedia('avatar')->delete();
                    }
                    $mediaItem->copy($user, 'avatar');
                }
                $user = $this->userRepository->update($input, $id);

                foreach (getCustomFieldsValues($customFields, $request) as $value) {
                    $user->customFieldsValues()
                        ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
                }
            }
        } catch (ValidatorException $e) {

            return $this->sendError($e->getMessage(), 422);
        }

        return $this->sendResponse($user, __('lang.updated_successfully', ['operator' => __('lang.user')]));
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
                'home_section_1' => '',
                'home_section_2' => '',
                'home_section_3' => '',
                'home_section_4' => '',
                'home_section_5' => '',
                'home_section_6' => '',
                'home_section_7' => '',
                'home_section_8' => '',
                'home_section_9' => '',
                'home_section_10' => '',
                'home_section_11' => '',
                'home_section_12' => '',
                'home_section_13' => '',
                'home_section_14' => '',
                'min_payout_amount' => '',
                'delivery_time_per_km' => '',
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
            return $this->sendError('Reset link not sent', 401);
        }

    }

    function driverLogin(Request $request)
    {
        try {

            $phone = $request->input('phone');
            $device_token = $request->input('device_token');
            $device_type = $request->input('device_type');
            $countryCode = $request->input('country_code');

            if (is_numeric($phone)) {
                $user = User::where('phone', $phone)->orWhere('phone', '91' . $phone)->first();
                if ($user) {
                    if ($user->hasRole('driver')) {
                        Auth::login($user);
                    } else {
                        return $this->sendError("Please login with another mobile number to login as driver", 403);
                    }
                } else {

                    $user = new User();
                    $user->phone = $phone;
                    $user->country_code = $countryCode;
                    $user->syncRoles('driver');

                }
            }

            $user->device_token = $device_token;
            $user->device_type = $device_type;
            $user->save();
            $userDevice = UserDevice::where('user_id', $user->id)->first();
            if (!$userDevice) {
                $userDevice = new UserDevice();
            }

            $userDevice->user_id = $user->id;
            $userDevice->device_type = $device_type;
            $userDevice->fcm_token = $device_token;
            $userDevice->save();

            PersonalAccessToken::query()
                ->where('tokenable_id', $user->id)
                ->delete();

            $token = $user->createToken('accessToken')->plainTextToken;

            $user->api_token = $token;
            $user->save();

            return $this->sendResponse(['user' => $user, 'token' => $token], 'You are successfully logged in');

        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
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
            $countryCode = $request->input('country_code');

            if (is_numeric($phone)) {
                $user = User::where('phone', $phone)->orWhere('phone', '91' . $phone)->first();
                if ($user) {

                    if ($user->hasRole('driver') || $user->hasRole('vendor_owner')) {
                        return $this->sendError(
                            "Number already registered, Please login using Different mobile number",
                            403
                        );
                    }
                } else {
                    $user = new User();
                    $user->phone = $phone;
                    $user->country_code = $countryCode;

                }
            }

            User::query()
                ->where('device_token', $device_token)
                ->where('id', '!=', $user->id)
                ->update(['device_token' => null]);

            $user->device_token = $device_token;
            $user->device_type = $device_type;
            $user->save();
            $user->syncRoles(['customer']);

            $user = User::find($user->id);

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

    public function profileImageUpload(Request $request)
    {
        $userId = $request->user_id;
        $file = $request->file('file');

        $user = User::find($userId);

        if ($file) {
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/profile-images', $filename);
            $user->profile_image = $filename;
            $user->save();
        }

        if (!$file) {
            $user->profile_image = null;
            $user->update();
        }


        return $this->sendResponse($user, 'Image updated successfully');
    }
}
