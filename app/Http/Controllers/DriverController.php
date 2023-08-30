<?php

namespace App\Http\Controllers;

use App\Criteria\Users\DriversCriteria;
use App\DataTables\DriverDataTable;
use App\Events\UserRoleChangedEvent;
use App\Http\Requests;
use App\Http\Requests\CreateDriverRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Models\Driver;
use App\Models\DriverBankDetail;
use App\Models\DriverDocument;
use App\Models\DriverPersonalDetail;
use App\Models\DriversCurrentLocation;
use App\Models\User;
use App\Repositories\CircleRepository;
use App\Repositories\CityRepository;
use App\Repositories\DriverRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\PickUpVehicleRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Kreait\Firebase\Contract\Database;
use Laracasts\Flash\Flash;
use Prettus\Validator\Exceptions\ValidatorException;

class DriverController extends Controller
{
    /** @var  DriverRepository */
    private $driverRepository;

    /** @var  CityRepository */
    private $cityRepository;

    /** @var  CircleRepository */
    private $circleRepository;

    /** @var  PickUpVehicleRepository */
    private $pickUpVehicleRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;
    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    /**
  * @var UserRepository
  */

private $userRepository;

    public function __construct(DriverRepository $driverRepo, CustomFieldRepository $customFieldRepo , UserRepository $userRepo,
    UploadRepository $uploadRepo, RoleRepository $roleRepo, CityRepository $cityRepository, CircleRepository $circleRepository,
                                PickUpVehicleRepository $pickUpVehicleRepository, Database $database)
    {
        parent::__construct();
        $this->driverRepository = $driverRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->userRepository = $userRepo;
        $this->uploadRepository = $uploadRepo;
        $this->roleRepository = $roleRepo;
        $this->cityRepository = $cityRepository;
        $this->circleRepository = $circleRepository;
        $this->pickUpVehicleRepository = $pickUpVehicleRepository;
        $this->database = $database;
        $this->table = 'user_locations';
    }

    /**
     * Display a listing of the Driver.
     *
     * @param DriverDataTable $driverDataTable
     * @return Response
     */
    public function index(DriverDataTable $driverDataTable)
    {
        return $driverDataTable->render('drivers.index');
    }

    /**
     * Show the form for creating a new Driver.
     *
     * @return Response
     */
    public function create()
    {
        $hasCustomField = in_array($this->userRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->userRepository->model());
            $html = generateCustomField($customFields);
        }

        $cities = $this->cityRepository->pluck('name', 'id');
        $vehicles = $this->pickUpVehicleRepository->pluck('name', 'id');

        return view('drivers.create')
            ->with('cities', $cities)
            ->with('vehicles', $vehicles)
            ->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Store a newly created Driver in storage.
     *
     * @param CreateDriverRequest $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|string|max:255|unique:users,email',
            'phone' => 'required|numeric|unique:users,phone',
            'city_id' => 'required',
            'circle_id' => 'required',
            'vehicle_id' => 'required',
            'password' => 'required|string|min:6',
            'delivery_fee' => 'required',
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $input['api_token'] = str_random(60);
        $input['area_id'] = $request->circle_id;

        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->userRepository->model());
        try {
            $user = $this->userRepository->create($input);
            $user->syncRoles($input['roles']);
            $user->customFieldsValues()->createMany(getCustomFieldsValues($customFields,$request));

            if (isset($input['avatar']) && $input['avatar']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['avatar']);
                $mediaItem = $cacheUpload->getMedia('avatar')->first();
                $mediaItem->copy($user, 'avatar');
            }

            $deliveryFee = $request->input('delivery_fee');
            event(new UserRoleChangedEvent($user));

            $driver = Driver::where('user_id', $user->id)->first();
            if(!$driver){
                $driver = new Driver();
            }
            $driver->delivery_fee = $deliveryFee;
            $driver->additional_amount = $request->additional_amount;
            $driver->base_distance = $request->base_distance;
            $driver->user_id = $user->id;
            $driver->admin_approved = true;
            $driver->city_id = $request->city_id;
            $driver->circle_id = $request->circle_id;
            $driver->vehicle_id = $request->vehicle_id;
            $driver->available = $request->filled('available') ?? 0;
            $driver->save();

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully',['operator' => __('lang.driver')]));

        return redirect(route('drivers.index'));
    }

    /**
     * Display the specified Driver.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $driver = $this->driverRepository->findWithoutFail($id);

        if (empty($driver)) {
            Flash::error('Driver not found');

            return redirect(route('drivers.index'));
        }

        $driverPersonalDetail = DriverPersonalDetail::where('user_id', $driver->user_id)->first();
        $driverBankDetail = DriverBankDetail::where('user_id', $driver->user_id)->first();
        $driverDocument = DriverDocument::where('user_id', $driver->user_id)->first();


        return view('drivers.show')->with('driver', $driver)
            ->with("driverPersonalDetail", $driverPersonalDetail)
            ->with("driverBankDetail", $driverBankDetail)
            ->with("driverDocument", $driverDocument);
    }

    /**
     * Show the form for editing the specified Driver.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $driver = $this->driverRepository->findWithoutFail($id);
        $userId = $driver->user_id;
        $user = $this->userRepository->findWithoutFail($userId);

        if (empty($user)) {
            Flash::error(__('lang.not_found',['operator' => __('lang.driver')]));

            return redirect(route('drivers.index'));
        }
        $customFieldsValues = $user->customFieldsValues()->with('customField')->get();
        $customFields =  $this->customFieldRepository->findByField('custom_field_model', $this->userRepository->model());
        $hasCustomField = in_array($this->userRepository->model(),setting('custom_field_models',[]));
        if($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        $cities = $this->cityRepository->pluck('name', 'id');
        $vehicles = $this->pickUpVehicleRepository->pluck('name', 'id');

        return view('drivers.edit')
            ->with("customFields", isset($html) ? $html : false)
           ->with("user", $user)
            ->with('cities', $cities)
            ->with('vehicles', $vehicles)
            ->with("driver", $driver);

    }

    /**
     * Update the specified Driver in storage.
     *
     * @param  int              $id
     * @param UpdateDriverRequest $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $user = $this->userRepository->findWithoutFail($id);
        $driver = $this->driverRepository->where('user_id',$user->id)->first();
        $input['area_id'] = $request->circle_id;

        if (empty($user)) {
            Flash::error('Driver not found');
            return redirect(route('drivers.index'));
        }
        $input = $request->all();
//        $input['password'] = Hash::make($input['password']);
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->userRepository->model());
        try {

            $user = $this->userRepository->update($input, $user->id);

            $driver->delivery_fee = $request->input('delivery_fee');
            $driver->available = $request->filled('available') ?? 0;
            $driver->city_id = $request->city_id;
            $driver->circle_id = $request->circle_id;
            $driver->vehicle_id = $request->vehicle_id;
            $driver->additional_amount = $request->additional_amount;
            $driver->base_distance = $request->base_distance;
            $driver->save();

            foreach (getCustomFieldsValues($customFields, $request) as $value){
                $user->customFieldsValues()
                    ->updateOrCreate(['custom_field_id'=>$value['custom_field_id']],$value);
            }
            if (isset($input['avatar']) && $input['avatar']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['avatar']);
                $mediaItem = $cacheUpload->getMedia('avatar')->first();
                $mediaItem->copy($user, 'avatar');
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully',['operator' => __('lang.driver')]));

        return redirect(route('drivers.index'));
    }

    /**
     * Remove the specified Driver from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $driver = $this->driverRepository->findWithoutFail($id);

        if (empty($driver)) {
            Flash::error('Driver not found');

            return redirect(route('drivers.index'));
        }

        $this->driverRepository->delete($id);

        $user = User::find($driver->user_id);
        $user->userDevices()->delete();
        $user->delete();
        
        Flash::success(__('lang.deleted_successfully',['operator' => __('lang.driver')]));

        return redirect(route('drivers.index'));
    }

        /**
     * Remove Media of Driver
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $driver = $this->driverRepository->findWithoutFail($input['id']);
        try {
            if($driver->hasMedia($input['collection'])){
                $driver->getFirstMedia($input['collection'])->delete();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
