<?php

namespace App\Http\Controllers;

use App\DataTables\AttributeDataTable;
use App\DataTables\AttributeOptionDataTable;
use App\Http\Requests\CreateAttributeOptionRequest;
use App\Http\Requests\CreateAttributesRequest;
use App\Http\Requests\UpdateAttributeOptionRequest;
use App\Http\Requests\UpdateAttributeRequest;
use App\Models\Product;
use App\Models\ProductAttributeOption;
use App\Repositories\AttributeOptionRepository;
use App\Repositories\AttributesRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\FieldRepository;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;
use Prettus\Validator\Exceptions\ValidatorException;

class AttributeOptionController extends Controller
{
    /** @var  AttributeOptionRepository */
    private $attributeOptionRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var AttributesRepository
     */
    private $attributesRepository;

    public function __construct(AttributeOptionRepository $attributeOptionRepository, CustomFieldRepository $customFieldRepo,
                                AttributesRepository $attributesRepository)
    {
        parent::__construct();
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->customFieldRepository = $customFieldRepo;
        $this->attributesRepository = $attributesRepository;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(AttributeOptionDataTable $attributeOptionDataTable)
    {
        return $attributeOptionDataTable->render('attribute_options.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $attribute = $this->attributesRepository->pluck('name', 'id');

        $hasCustomField = in_array($this->attributeOptionRepository->model(),setting('custom_field_models',[]));
        if($hasCustomField){
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->attributeOptionRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('attribute_options.create')->with("customFields", isset($html) ? $html : false)->with('attribute', $attribute);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateAttributeOptionRequest $request)
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->attributeOptionRepository->model());
        try {
            $attributeOption = $this->attributeOptionRepository->create($input);
            $attributeOption->customFieldsValues()->createMany(getCustomFieldsValues($customFields,$request));

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully',['operator' => __('lang.attribute_option')]));

        return redirect(route('attributeOptions.index'));
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
        $attributeOption = $this->attributeOptionRepository->findWithoutFail($id);
        $attribute = $this->attributesRepository->pluck('name', 'id');

        if (empty($attributeOption)) {
            Flash::error(__('lang.not_found',['operator' => __('lang.attribute')]));

            return redirect(route('attributeOptions.index'));
        }
        $customFieldsValues = $attributeOption->customFieldsValues()->with('customField')->get();
        $customFields =  $this->customFieldRepository->findByField('custom_field_model', $this->attributeOptionRepository->model());
        $hasCustomField = in_array($this->attributeOptionRepository->model(),setting('custom_field_models',[]));
        if($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('attribute_options.edit')->with('attribute', $attribute)->with("customFields", isset($html) ? $html : false)
            ->with('attributeOption', $attributeOption);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, UpdateAttributeOptionRequest $request)
    {
        $attributeOption = $this->attributeOptionRepository->findWithoutFail($id);

        if (empty($attributeOption)) {
            Flash::error('Attribute option not found');
            return redirect(route('attributeOptions.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->attributeOptionRepository->model());
        try {
            $attribute = $this->attributeOptionRepository->update($input, $id);


            foreach (getCustomFieldsValues($customFields, $request) as $value){
                $attribute->customFieldsValues()
                    ->updateOrCreate(['custom_field_id'=>$value['custom_field_id']],$value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully',['operator' => __('lang.attribute_option')]));

        return redirect(route('attributeOptions.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $attributeOption = $this->attributeOptionRepository->findWithoutFail($id);

        if (empty($attributeOption)) {
            Flash::error('Attribute option not found');

            return redirect(route('attributeOptions.index'));
        }

        $productIds =  ProductAttributeOption::where('attribute_option_id', $id)->pluck('product_id')->toArray();
        ProductAttributeOption::where('attribute_option_id', $id)->delete();
        Product::whereIn('id', $productIds)->delete();

        $this->attributeOptionRepository->delete($id);

        Flash::success(__('lang.deleted_successfully',['operator' => __('lang.attribute_option')]));

        return redirect(route('attributeOptions.index'));
    }
}
