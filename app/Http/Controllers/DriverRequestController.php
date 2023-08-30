<?php

namespace App\Http\Controllers;

use App\DataTables\DriverRequestDataTable;
use App\Models\DriverBankDetail;
use App\Models\DriverDocument;
use App\Models\DriverPersonalDetail;
use App\Models\User;
use App\Repositories\CustomFieldRepository;
use App\Repositories\DriverRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Laracasts\Flash\Flash;
use Prettus\Validator\Exceptions\ValidatorException;


class DriverRequestController extends Controller
{
    /** @var  DriverRepository */
    private $driverRepository;

    /**
     * @var UserRepository
     */

    private $userRepository;

    public function __construct(DriverRepository $driverRepo, UserRepository $userRepo)
    {
        parent::__construct();
        $this->driverRepository = $driverRepo;
        $this->userRepository = $userRepo;
    }

    public function index(DriverRequestDataTable $driverRequestDataTable)
    {
        return $driverRequestDataTable->render('driver-requests.index');
    }

    public function edit($id)
    {
        $driver = $this->driverRepository->findWithoutFail($id);
        $userId = $driver->user_id;
        $user = $this->userRepository->findWithoutFail($userId);

        if (empty($user)) {
            Flash::error(__('lang.not_found',['operator' => __('lang.driver')]));

            return redirect(route('driver-requests.index'));
        }

        return view('driver-requests.edit')
            ->with("customFields", isset($html) ? $html : false)
            ->with("user", $user)
            ->with("driver", $driver);
    }

    public function update($id, Request $request)
    {
        $user = $this->userRepository->findWithoutFail($id);
        $driver = $this->driverRepository->where('user_id',$user->id)->first();

        if (empty($user)) {
            Flash::error('Driver not found');
            return redirect(route('driver-requests.index'));
        }
        $input = $request->all();
        if($request->input('admin_approved')){
            $input['driver_signup_status'] = User::DRIVER_ADMIN_APPROVED;
        }
        try {

            $user = $this->userRepository->update($input, $user->id);
            $driver->delivery_fee = $request->input('delivery_fee');
            $driver->available = $request->input('available') ? 1 : 0;
            $driver->admin_approved = $request->input('admin_approved',0);
            $driver->save();

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully',['operator' => __('lang.driver')]));

        return redirect(route('driver-requests.index'));
    }

    public function show($id)
    {
        $driver = $this->driverRepository->findWithoutFail($id);

        $driverPersonalDetail = DriverPersonalDetail::where('user_id', $driver->user_id)->first();
        $driverBankDetail = DriverBankDetail::where('user_id', $driver->user_id)->first();
        $driverDocument = DriverDocument::where('user_id', $driver->user_id)->first();


        if (empty($driver)) {
            Flash::error('Driver not found');

            return redirect(route('driver-requests.index'));
        }

        return view('driver-requests.show')
            ->with('driver', $driver)
            ->with('driverPersonalDetail', $driverPersonalDetail)
            ->with('driverBankDetail', $driverBankDetail)
            ->with('driverDocument', $driverDocument);
    }

}
