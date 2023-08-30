<?php

namespace App\Http\Controllers\API;

use App\Models\PickUpVehicle;
use App\Http\Controllers\Controller;
use App\Repositories\PickUpVehicleRepository;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class PickUpVehicleAPIController extends Controller
{
    /** @var  PickUpVehicleRepository */
    private $pickUpVehicleRepository;

    public function __construct(PickUpVehicleRepository $pickUpVehicleRepository)
    {
        $this->pickUpVehicleRepository = $pickUpVehicleRepository;
    }

    /**
     * Display a listing of the Option.
     * GET|HEAD /options
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $this->pickUpVehicleRepository->pushCriteria(new RequestCriteria($request));
            $this->pickUpVehicleRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        $pickUpVehicles = $this->pickUpVehicleRepository->all();

        return $this->sendResponse($pickUpVehicles->toArray(), 'Pick-up vehicles retrieved successfully');
    }

}
