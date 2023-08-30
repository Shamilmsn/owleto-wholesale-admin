<?php

namespace App\Http\Controllers;

use App\DataTables\PickUpVehicleDataTable;
use App\Models\PickUpVehicle;
use App\Repositories\CustomFieldRepository;
use App\Repositories\PickUpVehicleRepository;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;
use Prettus\Validator\Exceptions\ValidatorException;

class PickUpVehicleController extends Controller
{
    /** @var  PickUpVehicleRepository */
    private $pickUpVehicleRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;


    public function __construct(PickUpVehicleRepository $pickUpVehicleRepository, CustomFieldRepository $customFieldRepo)
    {
        parent::__construct();
        $this->pickUpVehicleRepository = $pickUpVehicleRepository;
        $this->customFieldRepository = $customFieldRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(PickUpVehicleDataTable $pickUpVehicleDataTable)
    {
        return $pickUpVehicleDataTable->render('pickup-vehicles.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pickup-vehicles.create');
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

        try {
            $pickupVehicle = $this->pickUpVehicleRepository->create($input);

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.pickup_vehicle')]));

        return redirect(route('pick-up-vehicles.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PickUpVehicle  $pickUpVehicle
     * @return \Illuminate\Http\Response
     */
    public function show(PickUpVehicle $pickUpVehicle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PickUpVehicle  $pickUpVehicle
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pickupVehicle = $this->pickUpVehicleRepository->findWithoutFail($id);

        if (empty($pickupVehicle)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.pickup_vehicle_plural')]));
            return redirect(route('pick-up-vehicles.index'));
        }

        return view('pickup-vehicles.edit')->with('pickupVehicle', $pickupVehicle);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PickUpVehicle  $pickUpVehicle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $oldPickUp = $this->pickUpVehicleRepository->findWithoutFail($id);

        if (empty($oldPickUp)) {
            Flash::error('Pickup vehicle not found');
            return redirect(route('pick-up-vehicles.index'));
        }
        $input = $request->all();

        try {
            $market = $this->pickUpVehicleRepository->update($input, $id);
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.pickup_vehicle')]));

        return redirect(route('pick-up-vehicles.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PickUpVehicle  $pickUpVehicle
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $vehicle = $this->pickUpVehicleRepository->findWithoutFail($id);

        if (empty($vehicle)) {
            Flash::error('Pickup vehicle not found');

            return redirect(route('pick-up-vehicles.index'));
        }

        $this->pickUpVehicleRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.pickup_vehicle')]));

        return redirect(route('pick-up-vehicles.index'));
    }
}
