<?php

namespace App\Http\Controllers;

use App\DataTables\DeliveryTypeDataTable;
use App\DataTables\FieldDataTable;
use App\Http\Requests\CreateDeliveryTypeRequest;
use App\Http\Requests\CreateFieldRequest;
use App\Http\Requests\UpdateDeliveryTypeRequest;
use App\Http\Requests\UpdateFieldRequest;
use App\Models\DeliveryType;
use App\Repositories\CustomFieldRepository;
use App\Repositories\DeliveryTypeRepository;
use App\Repositories\FieldRepository;
use App\Repositories\MarketRepository;
use App\Repositories\UploadRepository;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;
use Prettus\Validator\Exceptions\ValidatorException;

class DeliveryTypeController extends Controller
{
    /** @var  DeliveryTypeRepository */
    private $deliveryTypeRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;


    public function __construct(DeliveryTypeRepository $deliveryTypeRepository, CustomFieldRepository $customFieldRepo )
    {
        parent::__construct();
        $this->customFieldRepository = $customFieldRepo;
        $this->deliveryTypeRepository = $deliveryTypeRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(DeliveryTypeDataTable $deliveryTypeDataTable)
    {
        return $deliveryTypeDataTable->render('delivery_types.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $hasCustomField = in_array($this->deliveryTypeRepository->model(),setting('custom_field_models',[]));
        if($hasCustomField){
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->deliveryTypeRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('delivery_types.create')->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateDeliveryTypeRequest $request)
    {
        $input = $request->all();

        $input['is_sloted'] = $request->is_sloted ? 1 : 0;
        $input['start_at'] = $request->is_sloted ? $request->start_at : null;
        $input['end_at'] = $request->is_sloted ? $request->end_at : null;

        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->deliveryTypeRepository->model());
        try {
            $deliveryType = $this->deliveryTypeRepository->create($input);
            $deliveryType->customFieldsValues()->createMany(getCustomFieldsValues($customFields,$request));
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully',['operator' => __('lang.field')]));

        return redirect(route('deliveryTypes.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $deliveryType = $this->deliveryTypeRepository->findWithoutFail($id);

        if (empty($deliveryType)) {
            Flash::error(__('lang.not_found',['operator' => __('lang.field')]));

            return redirect(route('deliveryTypes.index'));
        }
        $customFieldsValues = $deliveryType->customFieldsValues()->with('customField')->get();
        $customFields =  $this->customFieldRepository->findByField('custom_field_model', $this->deliveryTypeRepository->model());
        $hasCustomField = in_array($this->deliveryTypeRepository->model(),setting('custom_field_models',[]));
        if($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }
        return view('delivery_types.edit')->with('deliveryType', $deliveryType)->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, UpdateDeliveryTypeRequest $request)
    {
        $deliveryType = $this->deliveryTypeRepository->findWithoutFail($id);

        if (empty($deliveryType)) {
            Flash::error('Delivery Type not found');
            return redirect(route('deliveryTypes.index'));
        }
        $input = $request->all();

        $input['is_sloted'] = $request->is_sloted ? 1 : 0;
        $input['start_at'] = $request->is_sloted ? $request->start_at : null;
        $input['end_at'] = $request->is_sloted ? $request->end_at : null;
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->deliveryTypeRepository->model());
        try {
            $deliveryType = $this->deliveryTypeRepository->update($input, $id);
            foreach (getCustomFieldsValues($customFields, $request) as $value){
                $deliveryType->customFieldsValues()
                    ->updateOrCreate(['custom_field_id'=>$value['custom_field_id']],$value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully',['operator' => __('lang.field')]));

        return redirect(route('deliveryTypes.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deliveryType = $this->deliveryTypeRepository->findWithoutFail($id);

        if (empty($deliveryType)) {
            Flash::error('Delivery Type not found');

            return redirect(route('deliveryTypes.index'));
        }

        $deliveryType->sectors()->detach();
        $this->deliveryTypeRepository->delete($id);

        Flash::success(__('lang.deleted_successfully',['operator' => __('lang.field')]));

        return redirect(route('deliveryTypes.index'));
    }
}
