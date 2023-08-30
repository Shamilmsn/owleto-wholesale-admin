<?php

namespace App\Http\Controllers;

use App\DataTables\AttributeDataTable;
use App\Http\Requests\CreateAttributesRequest;
use App\Http\Requests\UpdateAttributeRequest;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Product;
use App\Models\ProductAttributeOption;
use App\Models\ProductOrder;
use App\Repositories\AttributesRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\FieldRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laracasts\Flash\Flash;
use Prettus\Validator\Exceptions\ValidatorException;

class AttributeController extends Controller
{
    /** @var  AttributesRepository */
    private $attributesRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var FieldRepository
     */
    private $fieldRepository;

    public function __construct(AttributesRepository $attributesRepository, CustomFieldRepository $customFieldRepo,
    FieldRepository $fieldRepository)
    {
        parent::__construct();
        $this->attributesRepository = $attributesRepository;
        $this->customFieldRepository = $customFieldRepo;
        $this->fieldRepository = $fieldRepository;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(AttributeDataTable $attributeDataTable)
    {
        return $attributeDataTable->render('attributes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sector = $this->fieldRepository->pluck('name', 'id');

        $meta = Attribute::$meta;

        $hasCustomField = in_array($this->attributesRepository->model(),setting('custom_field_models',[]));
        if($hasCustomField){
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->attributesRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('attributes.create')->with("customFields", isset($html) ? $html : false)->with('sector', $sector)
            ->with('meta',$meta);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateAttributesRequest $request)
    {
        $input = $request->all();
//       return $input;
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->attributesRepository->model());
        try {
            $attributes = $this->attributesRepository->create($input);
            $attributes->customFieldsValues()->createMany(getCustomFieldsValues($customFields,$request));

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully',['operator' => __('lang.attribute')]));

        return redirect(route('attributes.index'));
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
        $attribute = $this->attributesRepository->findWithoutFail($id);
        $sector = $this->fieldRepository->pluck('name', 'id');
        $meta = Attribute::$meta;


        if (empty($attribute)) {
            Flash::error(__('lang.not_found',['operator' => __('lang.attribute')]));

            return redirect(route('attributes.index'));
        }
        $customFieldsValues = $attribute->customFieldsValues()->with('customField')->get();
        $customFields =  $this->customFieldRepository->findByField('custom_field_model', $this->attributesRepository->model());
        $hasCustomField = in_array($this->attributesRepository->model(),setting('custom_field_models',[]));
        if($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('attributes.edit')->with('attribute', $attribute)->with("customFields", isset($html) ? $html : false)
            ->with('sector', $sector)->with('meta', $meta);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, UpdateAttributeRequest $request)
    {
        $attribute = $this->attributesRepository->findWithoutFail($id);

        if (empty($attribute)) {
            Flash::error('Attribute not found');
            return redirect(route('attributes.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->attributesRepository->model());
        try {
            $attribute = $this->attributesRepository->update($input, $id);


            foreach (getCustomFieldsValues($customFields, $request) as $value){
                $attribute->customFieldsValues()
                    ->updateOrCreate(['custom_field_id'=>$value['custom_field_id']],$value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully',['operator' => __('lang.attribute')]));

        return redirect(route('attributes.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $attribute = $this->attributesRepository->findWithoutFail($id);

        if (empty($attribute)) {
            Flash::error('Attribute not found');

            return redirect(route('attributes.index'));
        }

        AttributeOption::where('attribute_id', $id)->delete();
        $productIds =  ProductAttributeOption::where('attribute_id', $id)->pluck('product_id')->toArray();
        ProductAttributeOption::where('attribute_id', $id)->delete();
        Product::whereIn('id', $productIds)->delete();

        $this->attributesRepository->delete($id);

        Flash::success(__('lang.deleted_successfully',['operator' => __('lang.attribute')]));

        return redirect(route('attributes.index'));
    }

    /**
     * Remove Media of Attribute
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $attribute = $this->attributesRepository->findWithoutFail($input['id']);
        try {
            if($attribute->hasMedia($input['collection'])){
                $attribute->getFirstMedia($input['collection'])->delete();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
