<?php

namespace App\Http\Controllers\API;


use App\Criteria\DeliveryAddress\DeliveryAddressOfUserCriteria;
use App\Http\Controllers\Controller;
use App\Models\DeliveryAddress;
use App\Repositories\DeliveryAddressRepository;
use Flash;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class DeliveryAddressController
 * @package App\Http\Controllers\API
 */
class DeliveryAddressAPIController extends Controller
{
    /** @var  DeliveryAddressRepository */
    private $deliveryAddressRepository;

    public function __construct(DeliveryAddressRepository $deliveryAddressRepo)
    {
        $this->deliveryAddressRepository = $deliveryAddressRepo;
    }

    /**
     * Display a listing of the DeliveryAddress.
     * GET|HEAD /deliveryAddresses
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $this->deliveryAddressRepository->pushCriteria(new RequestCriteria($request));
            $this->deliveryAddressRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->deliveryAddressRepository->pushCriteria(new DeliveryAddressOfUserCriteria(auth()->id()));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $deliveryAddresses = $this->deliveryAddressRepository->all();

        return $this->sendResponse($deliveryAddresses->toArray(), 'Delivery Addresses retrieved successfully');
    }

    /**
     * Display the specified DeliveryAddress.
     * GET|HEAD /deliveryAddresses/{id}
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        /** @var DeliveryAddress $deliveryAddress */
        if (!empty($this->deliveryAddressRepository)) {
            $deliveryAddress = $this->deliveryAddressRepository->findWithoutFail($id);
        }

        if (empty($deliveryAddress)) {
            return $this->sendError('Delivery Address not found');
        }

        return $this->sendResponse($deliveryAddress->toArray(), 'Delivery Address retrieved successfully');
    }

    /**
     * Store a newly created DeliveryAddress in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $uniqueInput = $request->only("address");
        $otherInput = $request->except("address");

        if($otherInput['is_default'] == 1){
            $deliveryAddresses = DeliveryAddress::where('user_id', $otherInput['user_id'])->get();
            foreach ($deliveryAddresses as $deliveryAddress){
                $deliveryAddress->is_default = false;
                $deliveryAddress->save();
            }
        }

        try {

            $deliveryAddress = $this->deliveryAddressRepository->create([
                'description' => $otherInput['description'],
                'address' => $uniqueInput['address'],
                'latitude' => $otherInput['latitude'],
                'longitude' => $otherInput['longitude'],
                'house_number' => $otherInput['house_number'],
                'area' => $otherInput['area'],
                'direction_to_reach' => $otherInput['direction_to_reach'],
                'pincode' => $otherInput['direction_to_reach'],
                'address_as' => $otherInput['address_as'],
                'is_default' => $otherInput['is_default'],
                'user_id' => $otherInput['user_id'],
                'name' => $otherInput['name'],
                'phone' => $otherInput['phone'],
            ]);

//            $deliveryAddress = $this->deliveryAddressRepository->updateOrCreate($uniqueInput, $otherInput);

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($deliveryAddress->toArray(), __('lang.saved_successfully', ['operator' => __('lang.delivery_address')]));
    }

    /**
     * Update the specified DeliveryAddress in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $deliveryAddress = $this->deliveryAddressRepository->findWithoutFail($id);

        if (empty($deliveryAddress)) {
            return $this->sendError('Delivery Address not found');
        }
        $input = $request->all();

        if($input['is_default'] == 1){
            $deliveryAddresses = DeliveryAddress::where('user_id', $input['user_id'])->get();
            foreach ($deliveryAddresses as $deliveryAddress){
                $deliveryAddress->is_default = false;
                $deliveryAddress->save();
            }
        }

        try {

            $deliveryAddress->description = $input['description'];
            $deliveryAddress->address = $input['address'];
            $deliveryAddress->latitude = $input['latitude'];
            $deliveryAddress->longitude = $input['longitude'];
            $deliveryAddress->house_number = $input['house_number'];
            $deliveryAddress->area = $input['area'];
            $deliveryAddress->direction_to_reach = $input['direction_to_reach'];
            $deliveryAddress->pincode = $input['pincode'];
            $deliveryAddress->address_as = $input['address_as'];
            $deliveryAddress->is_default = $input['is_default'];
            $deliveryAddress->name = $input['name'];
            $deliveryAddress->phone = $input['phone'];
            $deliveryAddress->save();

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($deliveryAddress->toArray(), __('lang.updated_successfully', ['operator' => __('lang.delivery_address')]));

    }

    /**
     * Remove the specified Address from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $address = $this->deliveryAddressRepository->findWithoutFail($id);

        if (empty($address)) {
            return $this->sendError('Delivery Address Not found');

        }

        $this->deliveryAddressRepository->delete($id);

        return $this->sendResponse($address, __('lang.deleted_successfully',['operator' => __('lang.delivery_address')]));

    }
}