<?php

namespace App\Http\Controllers;

use App\DataTables\CategoryDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Market;
use App\Models\Product;
use App\Models\ProductAttributeOption;
use App\Repositories\CartRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\FieldRepository;
use App\Repositories\UploadRepository;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class CategoryController extends Controller
{
    /** @var  CategoryRepository */
    private $categoryRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var FieldRepository
     */
    private $fieldRepository;

    /**
  * @var UploadRepository
  */
private $uploadRepository;

    public function __construct(CategoryRepository $categoryRepo, CustomFieldRepository $customFieldRepo , UploadRepository $uploadRepo,
    FieldRepository $fieldRepository)
    {
        parent::__construct();
        $this->categoryRepository = $categoryRepo;
        $this->fieldRepository = $fieldRepository;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
    }

    /**
     * Display a listing of the Category.
     *
     * @param CategoryDataTable $categoryDataTable
     * @return Response
     */
    public function index(CategoryDataTable $categoryDataTable)
    {
        return $categoryDataTable->render('categories.index');
    }

    /**
     * Show the form for creating a new Category.
     *
     * @return Response
     */
    public function create()
    {
        
        
        $hasCustomField = in_array($this->categoryRepository->model(),setting('custom_field_models',[]));
            if($hasCustomField){
                $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->categoryRepository->model());
                $html = generateCustomField($customFields);
            }
            $sectors = $this->fieldRepository->get();

            $categories = Category::where('parent_id', null)->cursor();

        return view('categories.create')->with("customFields", isset($html) ? $html : false)
            ->with('sectors', $sectors)->with('categories', $categories);
    }

    /**
     * Store a newly created Category in storage.
     *
     * @param CreateCategoryRequest $request
     *
     * @return Response
     */
    public function store(CreateCategoryRequest $request)
    {
        $input = $request->all();
//        return $input;

        if($request->parent_id){
            $input['parent_id'] = $request->parent_id;
            $input['is_child'] = true;
        }
        else{
            $input['is_parent'] = true;
        }

//        if($request->filled('subcategory_checkbox')){
//            $input['field_id'] = null;
//        }
//        else{
//            $input['field_id'] = $request->field_id;
//        }

        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->categoryRepository->model());
        try {
            $category = $this->categoryRepository->create($input);
            $category->customFieldsValues()->createMany(getCustomFieldsValues($customFields,$request));
            if(isset($input['image']) && $input['image']){
            $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
            $mediaItem = $cacheUpload->getMedia('image')->first();
            $mediaItem->copy($category, 'image');
}
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully',['operator' => __('lang.category')]));

        return redirect(route('categories.index'));
    }

    /**
     * Display the specified Category.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $category = $this->categoryRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('Category not found');

            return redirect(route('categories.index'));
        }

        return view('categories.show')->with('category', $category);
    }

    /**
     * Show the form for editing the specified Category.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $category = $this->categoryRepository->with('parent')->findWithoutFail($id);
        $sectors = $this->fieldRepository->get();
        if (empty($category)) {
            Flash::error(__('lang.not_found',['operator' => __('lang.category')]));

            return redirect(route('categories.index'));
        }
        $customFieldsValues = $category->customFieldsValues()->with('customField')->get();
        $customFields =  $this->customFieldRepository->findByField('custom_field_model', $this->categoryRepository->model());
        $hasCustomField = in_array($this->categoryRepository->model(),setting('custom_field_models',[]));
        if($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('categories.edit')->with('category', $category)->with("customFields", isset($html) ? $html : false)
            ->with('sectors', $sectors);
    }

    /**
     * Update the specified Category in storage.
     *
     * @param  int              $id
     * @param UpdateCategoryRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateCategoryRequest $request)
    {
        $category = $this->categoryRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('Category not found');
            return redirect(route('categories.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->categoryRepository->model());
        try {
            $category = $this->categoryRepository->update($input, $id);
            
            if(isset($input['image']) && $input['image']){
    $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
    $mediaItem = $cacheUpload->getMedia('image')->first();
    $mediaItem->copy($category, 'image');
}
            foreach (getCustomFieldsValues($customFields, $request) as $value){
                $category->customFieldsValues()
                    ->updateOrCreate(['custom_field_id'=>$value['custom_field_id']],$value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully',['operator' => __('lang.category')]));

        return redirect(route('categories.index'));
    }

    /**
     * Remove the specified Category from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $category = $this->categoryRepository->findWithoutFail($id);

        $subCategories = Category::where('parent_id', $category->id)->get();

        if(count($subCategories)>0){
            Flash::error('Category cannot delete.');

            return redirect(route('categories.index'));
        }

        $products = Product::where('category_id', $id)->get();
        if(count($products)>0){
            Flash::error('Category cannot delete.');

            return redirect(route('categories.index'));
        }

        $marketCategories = Market::whereHas('market_categories', function ($query) use ($id){
            $query->where('category_id', $id);
        })->get();

        if(count($marketCategories)>0){
            Flash::error('Category cannot delete.');

            return redirect(route('categories.index'));
        }

        if (empty($category)) {
            Flash::error('Category not found');

            return redirect(route('categories.index'));
        }

        $this->categoryRepository->delete($id);

        Flash::success(__('lang.deleted_successfully',['operator' => __('lang.category')]));

        return redirect(route('categories.index'));
    }

        /**
     * Remove Media of Category
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $category = $this->categoryRepository->findWithoutFail($input['id']);
        try {
            if($category->hasMedia($input['collection'])){
                $category->getFirstMedia($input['collection'])->delete();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function subCategories(Request $request, $id)
    {
        $categories = Category::where('parent_id', $id)->get();

        return response()->json($categories, 200);
    }

    public function getCategories($sectorId)
    {
        $categories = Category::where('field_id', $sectorId)
            ->where('parent_id', null)
            ->get();


        return response()->json($categories, 200);
    }
}
