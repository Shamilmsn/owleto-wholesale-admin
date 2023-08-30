<?php
/**
 * File name: ProductController.php
 * Last modified: 2020.04.30 at 08:21:08
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers;

use App\Criteria\Products\ProductsOfUserCriteria;
use App\DataTables\ProductDataTable;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\AttributeOption;
use App\Models\Field;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\ProductAttributeOption;
use App\Repositories\AttributeOptionRepository;
use App\Repositories\AttributesRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\DaysRepository;
use App\Repositories\DeliveryTimeRepository;
use App\Repositories\FieldRepository;
use App\Repositories\MarketRepository;
use App\Repositories\ProductAttributeOptionRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UploadRepository;
use Carbon\Carbon;
use Exception;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;
use WpOrg\Requests\Auth;

class ProductController extends Controller
{
    /** @var  ProductRepository */
    private $productRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;
    /**
     * @var MarketRepository
     */
    private $marketRepository;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /** @var  DeliveryTimeRepository */
    private $deliveryTimeRepository;

    /** @var  DaysRepository */
    private $daysRepository;

    /** @var  AttributesRepository */
    private $attributesRepository;

    /** @var  AttributeOptionRepository */
    private $attributeOptionRepository;

    /** @var  ProductAttributeOptionRepository */
    private $productAttributeOptionRepository;

    /** @var  FieldRepository */
    private $fieldRepository;

    public function __construct(
        ProductRepository                $productRepo,
        CustomFieldRepository            $customFieldRepo,
        UploadRepository                 $uploadRepo,
        MarketRepository                 $marketRepo,
        CategoryRepository               $categoryRepo,
        DeliveryTimeRepository           $deliveryTimeRepo,
        DaysRepository                   $daysRepo,
        AttributesRepository             $attributesRepository,
        AttributeOptionRepository        $attributeOptionRepository,
        ProductAttributeOptionRepository $productAttributeOptionRepository,
        FieldRepository                  $fieldRepository
    )
    {
        parent::__construct();
        $this->productRepository = $productRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->marketRepository = $marketRepo;
        $this->categoryRepository = $categoryRepo;
        $this->deliveryTimeRepository = $deliveryTimeRepo;
        $this->daysRepository = $daysRepo;
        $this->attributesRepository = $attributesRepository;
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->productAttributeOptionRepository = $productAttributeOptionRepository;
        $this->fieldRepository = $fieldRepository;
    }

    /**
     * Display a listing of the Product.
     *
     * @param ProductDataTable $productDataTable
     * @return Response
     */
    public function index(ProductDataTable $productDataTable)
    {
        return $productDataTable->render('products.index');
    }

    /**
     * Store a newly created Product in storage.
     *
     * @param CreateProductRequest $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $rules = [
            'base_name' => 'required',
            'price' => 'required|numeric|min:0',
            'market_id' => 'required|exists:markets,id',
            'category_id' => 'required|exists:categories,id',
        ];

        if(request()->user()->hasRole('admin')) {
            $rules['tax'] = 'required';
            $rules['owleto_commission_percentage'] = 'required';
        }

        $request->validate($rules);

        $sectors = [
            Field::RESTAURANT,
            Field::HOME_COOKED_FOOD,
            Field::INSTAPRENURES,
        ];

        $collection = collect($sectors);

        if (!$collection->contains($request->sector_id)) {
            if (!$request->sub_category_id) {
                Flash::error('sub category required');

                return redirect()->back()->withInput();
            }

        }

        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->productRepository->model());
        try {

            DB::beginTransaction();

            if ($input['price'] < $input['discount_price']) {
                Flash::error('Discount Price Should be less than Price Amount');
                return redirect()->back()->withInput();

            }
            if ($input['minimum_orders'] == null) {
                $input['minimum_orders'] = 0;
            }

            $product = $this->productRepository->create($input);
            $product->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($product, 'image');

                }
            }

            $product->is_refund_or_replace = (bool)$request->is_refund_or_replace;
            $product->return_days = $request->return_days;

            $product->is_base_product = Product::BASE_PRODUCT;
            if ($request->variant_product == Product::VARIANT_PRODUCT_AVAILABLE) {
                $product->product_type = Product::VARIANT_BASE_PRODUCT;
            } else {
                $product->product_type = Product::STANDARD_PRODUCT;
            }

            $product->is_variant_display_product =
                $request->is_variant_display_product == 0 ? 0 : 1;

            $owletoCommissionPercent = $request->owleto_commission_percentage;

            if($owletoCommissionPercent) {
                $price = $request->discount_price > 0 ? $request->discount_price : $request->price;
                $taxPercentage = $request->tax;
                $tdsPercentage = Product::TDS_PERCENTAGE;
                $tcsPercentage = Product::TCS_PERCENTAGE;

                $priceExcludingtax = ($price / (100+$taxPercentage)) * 100;

                if ($request->sector_id == Field::RESTAURANT ||
                    $request->sector_id == Field::HOME_COOKED_FOOD) {
                    $tdsAmount = 0;
                    $tcsAmount = 0;
                } else {
                    $tdsAmount = ($tdsPercentage / 100) * $priceExcludingtax;
                    $tcsAmount = ($tcsPercentage / 100) * $priceExcludingtax;
                }

                $owletoCommissionAmount = 0;
                if ($owletoCommissionPercent > 0) {
                    $owletoCommissionAmount = ($owletoCommissionPercent / 100) * $price;
                }

                $eightyPercentageOfCommissionAmount = (18/100)*$owletoCommissionAmount;
                $product->owleto_commission_amount = $owletoCommissionAmount;
                $product->eighty_percentage_of_commission_amount =
                    $eightyPercentageOfCommissionAmount;

                $vendorPayment = $price-$owletoCommissionAmount -
                    $tdsAmount -
                    $tdsAmount -
                    $eightyPercentageOfCommissionAmount;

                $product->price_without_gst = $priceExcludingtax;
                $product->tcs_percentage = $tcsPercentage;
                $product->tcs_amount = $tcsAmount;
                $product->tds_percentage = $tdsPercentage;
                $product->tds_amount = $tdsAmount;
                $product->vendor_payment_amount = $vendorPayment;
            }

            $product->featured = false;
            $product->save();

            if ($request->addon_name) {
                $addonNames = $request->addon_name;
                $addonPrices = $request->add_on_price;
                $addonLength = count($request->addon_name);

                for ($i = 0; $i < $addonLength; $i++) {
                    if ($addonNames[$i]) {
                        $productAddon = new ProductAddon();
                        $productAddon->product_id = $product->id;
                        $productAddon->name = $addonNames[$i];
                        $productAddon->price = $addonPrices[$i];
                        $productAddon->save();
                    }
                }
            }

            $parentID = $product->id;
            $days = $request->input('days');
            $attributeIDs = $request->input('attributes');

            if ($request->variant_product == Product::VARIANT_PRODUCT_AVAILABLE) {

                $index = 0;
                $variantProductPrices = $request->input('variant_product_price');
                $variantNames = $request->input('variant_product_name');
                $variantProductDiscountPrices = $request->input('variant_product_discount_price');
                $variantProductStocks = $request->input('variant_product_stock');
                if ($variantProductPrices != NULL) {

                    foreach ($variantProductPrices as $key => $variantProductPrice) {

                        $variantProduct = new Product();

                        if($key==0){
                            $variantProduct->is_variant_display_product =
                                $request->is_variant_display_product == 0 ? 0 : 1;
                        }

                        $variantProduct->base_name = $request->input('base_name');
                        $variantProduct->variant_name = $variantNames[$index];
                        $variantProduct->price = $variantProductPrice;

                        $variantProduct->discount_price = $variantProductDiscountPrices[$index];
                        $variantProduct->stock = $variantProductStocks[$index];
                        $variantProduct->description = $request->input('description');
                        $variantProduct->capacity = $request->input('capacity');
                        $variantProduct->package_items_count =
                            $request->input('package_items_count');
                        $variantProduct->unit = $request->input('unit');
                        $variantProduct->featured = false;
                        $variantProduct->deliverable = $request->input('deliverable');
                        $variantProduct->market_id = $request->input('market_id');
                        $variantProduct->category_id = $request->input('category_id');
                        $variantProduct->sub_category_id = $request->input('sub_category_id');
                        $variantProduct->is_base_product = Product::NOT_BASE_PRODUCT;
                        $variantProduct->product_type = Product::VARIANT_PRODUCT;
                        $variantProduct->parent_id = $parentID;
                        $variantProduct->sector_id = $request->input('sector_id');
                        $variantProduct->is_enabled = $request->input('is_enabled');
                        $variantProduct->minimum_orders = $request->minimum_orders ?
                            $request->minimum_orders
                            :  0;
                        $variantProduct->tax = $request->input('tax');
                        $variantProduct->is_refund_or_replace = $request->is_refund_or_replace;
                        $variantProduct->return_days = $request->return_days;
                        $variantProduct->food_type = $request->food_type;

                        $owletoCommissionPercent = $request->owleto_commission_percentage;

                        if($owletoCommissionPercent) {
                            $price = $variantProductDiscountPrices[$index] > 0 ?
                                $variantProductDiscountPrices[$index] : $variantProductPrice;
                            $taxPercentage = $request->tax;
                            $tdsPercentage = Product::TDS_PERCENTAGE;
                            $tcsPercentage = Product::TCS_PERCENTAGE;

                            $priceExcludingtax = ($price / (100+$taxPercentage)) * 100;

                            if ($request->sector_id == Field::RESTAURANT ||
                                $request->sector_id == Field::HOME_COOKED_FOOD) {
                                $tdsAmount = 0;
                                $tcsAmount = 0;
                            } else {
                                $tdsAmount = ($tdsPercentage / 100) * $priceExcludingtax;
                                $tcsAmount = ($tcsPercentage / 100) * $priceExcludingtax;
                            }


                            $owletoCommissionAmount = 0;
                            if ($owletoCommissionPercent > 0) {
                                $owletoCommissionAmount = ($owletoCommissionPercent / 100) * $price;
                            }

                            $eightyPercentageOfCommissionAmount = (18/100)*$owletoCommissionAmount;
                            $product->owleto_commission_amount = $owletoCommissionAmount;
                            $product->eighty_percentage_of_commission_amount =
                                $eightyPercentageOfCommissionAmount;

                            $vendorPayment = $price-$owletoCommissionAmount-$tdsAmount-$tdsAmount-$eightyPercentageOfCommissionAmount;
                            $variantProduct->owleto_commission_percentage = $owletoCommissionPercent;
                            $variantProduct->owleto_commission_amount = $owletoCommissionAmount;
                            $variantProduct->price_without_gst = $priceExcludingtax;
                            $variantProduct->tcs_percentage = $tcsPercentage;
                            $variantProduct->tcs_amount = $tcsAmount;
                            $variantProduct->tds_percentage = $tdsPercentage;
                            $variantProduct->tds_amount = $tdsAmount;
                            $variantProduct->vendor_payment_amount = $vendorPayment;
                        }

                        $variantProduct->save();

                        if ($request->input('scheduled_delivery') == 1) {
                            $variantProduct->scheduled_delivery = $request->input('scheduled_delivery');
                            $variantProduct->order_start_time = $request->input('order_start_time');
                            $variantProduct->order_end_time = $request->input('order_end_time');
                            $variantProduct->delivery_time_id = $request->input('delivery_time_id');
                            $variantProduct->save();
                            if ($days != NULL) {
                                foreach ($days as $day) {
                                    $variantProduct
                                        ->days()
                                        ->attach($variantProduct->id, ['day_id' => $day]);
                                }
                            }
                        }

                        if (isset($input['image']) &&
                            $input['image'] &&
                            is_array($input['image'])) {
                            foreach ($input['image'] as $fileUuid) {
                                $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                                $mediaItem = $cacheUpload->getMedia('image')->first();
                                $mediaItem->copy($variantProduct, 'image');

                            }
                        }

                        $option = 0;
                        foreach ($attributeIDs as $attributeID) {
                            $attributeOptionIds = $request->input('attribute-option-' . $option);
                            $productAttributeOption = new ProductAttributeOption();
                            $productAttributeOption->product_id = $variantProduct->id;
                            $productAttributeOption->attribute_id = $attributeID;
                            $productAttributeOption->attribute_option_id = $attributeOptionIds[$index];
                            $productAttributeOption->base_product_id = $parentID;
                            $productAttributeOption->save();
                            $option++;
                        }

                        $index++;
                    }
                }
            }

            if ($request->input('sector_id') != 1) {
                $product->scheduled_delivery = false;
                $product->save();
            }

            DB::commit();
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        info("STORE PRODUCT : " . $product);


        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.product')]));

        return redirect(route('products.index'));
    }

    /**
     * Show the form for creating a new Product.
     *
     * @return Response
     */
    public function create()
    {

        $category = $this->categoryRepository->where('is_parent', true)->pluck('name', 'id');
        if (auth()->user()->hasRole('admin')) {

            $market = $this->marketRepository->pluck('name', 'id');

        } else {
            $market = $this->marketRepository->myActiveMarkets()->pluck('name', 'id');
        }
        $hasCustomField = in_array($this->productRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->productRepository->model());
            $html = generateCustomField($customFields);
        }

        $sector = $this->fieldRepository->pluck('name', 'id');
        $deliveryTime = $this->deliveryTimeRepository->pluck('name', 'id');
        $days = $this->daysRepository->pluck('name', 'id');
        $daysSelected = [];
        $attributes = $this->attributesRepository->has('attributeOptions')->pluck('name', 'id');
        $attributesSelected = [];
        $data = [];

        return view('products.create')->with("customFields", isset($html) ? $html : false)->with("market", $market)->with("category", $category)
            ->with("deliveryTime", $deliveryTime)->with("days", $days)->with("daysSelected", $daysSelected)
            ->with('attributes', $attributes)->with('attributesSelected', $attributesSelected)->with('data', $data)
            ->with('sector', $sector);
    }

    /**
     * Display the specified Product.
     *
     * @param int $id
     *
     * @return Response
     * @throws RepositoryException
     */
    public function show($id)
    {
        $this->productRepository->pushCriteria(new ProductsOfUserCriteria(auth()->id()));
        $product = $this->productRepository->findWithoutFail($id);

        if (empty($product)) {
            Flash::error('Product not found');

            return redirect(route('products.index'));
        }

        return view('products.show')->with('product', $product);
    }

    /**
     * Show the form for editing the specified Product.
     *
     * @param int $id
     *
     * @return Response
     * @throws RepositoryException
     */
    public function edit($id)
    {
        $product = $this->productRepository->findWithoutFail($id);
        info("EDIT PRODUCT : " . $product);
        $this->productRepository->pushCriteria(new ProductsOfUserCriteria(auth()->id()));
        if (empty($product)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.product')]));
            return redirect(route('products.index'));
        }
        $category = $this->categoryRepository->where('is_parent', true)->pluck('name', 'id');
        if (auth()->user()->hasRole('admin')) {

            $markets = $this->marketRepository->pluck('name', 'id');
        } else {
            $markets = $this->marketRepository->myMarkets()->pluck('name', 'id');
        }

        $customFieldsValues = $product->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->productRepository->model());
        $hasCustomField = in_array($this->productRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }
        $deliveryTime = $this->deliveryTimeRepository->pluck('name', 'id');
        $days = $this->daysRepository->pluck('name', 'id');
        $daysSelected = $product->days()->pluck('days.id')->toArray();

        $attributes = $this->attributesRepository->has('attributeOptions')->pluck('name', 'id');
        $attributesSelected = [];
        $data = [];
        $productAttributeOptions = [];
        $attributeOptions = $this->attributeOptionRepository->pluck('name', 'id');

        if ($product->parent_id) {
            $productAttributeOptions = $this->productAttributeOptionRepository->where('product_id', $id)->with('attribute', 'attributeOption')->get();
            $attributeIds = [];
            foreach ($productAttributeOptions as $productAttributeOption) {
                array_push($attributeIds, $productAttributeOption->attribute->id);
            }
            $count = count($attributeIds);
            $data = [];
            $index = 1;
            if ($count > 0) {
                foreach ($attributeIds as $attributeId) {
                    $data[$index] = AttributeOption::with('attribute')->where("attribute_id", $attributeId)
                        ->get();
                    $index++;
                }
            }
        }

        $availableVariantProducts = [];
        if ($product->is_base_product == Product::BASE_PRODUCT) {
            $availableVariantProducts = Product::where('parent_id', $id)->with('productAttributeOptions', 'productAttributeOptions.attributeOption')->get();
        }

        $productAddons = ProductAddon::where('product_id', $id)->get();

        $isDisplayProduct = 0;

        if ($product->product_type == Product::VARIANT_BASE_PRODUCT) {
            $variantProducts = Product::where('parent_id', $product->id)
                ->get();

            foreach ($variantProducts as $variantProduct) {
                if ($variantProduct->is_variant_display_product == 0) {
                    $isDisplayProduct = 1;
                }
            }
        }

        return view('products.edit')->with('product', $product)->with("customFields", isset($html) ? $html : false)->with("markets", $markets)
            ->with("category", $category)
            ->with("deliveryTime", $deliveryTime)->with("days", $days)->with("daysSelected", $daysSelected)
            ->with('attributes', $attributes)->with('attributesSelected', $attributesSelected)->with('data', $data)
            ->with('productAttributeOptions', $productAttributeOptions)->with('attributeOptions', $attributeOptions)
            ->with('availableVariantProducts', $availableVariantProducts)
            ->with('productAddons', $productAddons)
            ->with('isDisplayProduct', $isDisplayProduct);
    }

    /**
     * Update the specified Product in storage.
     *
     * @param int $id
     * @param UpdateProductRequest $request
     *
     * @return Response
     * @throws RepositoryException
     */
    public function update($id, Request $request)
    {
        $rules = [
            'base_name' => 'required',
            'price' => 'required|numeric|min:0',
            'market_id' => 'required|exists:markets,id',
            'category_id' => 'required|exists:categories,id',
        ];

        if(request()->user()->hasRole('admin')) {
            $rules['tax'] = 'required';
            $rules['owleto_commission_percentage'] = 'required';
        }

        $request->validate($rules);

        $sectors = [
            Field::RESTAURANT,
            Field::HOME_COOKED_FOOD,
            Field::INSTAPRENURES
        ];

        $collection = collect($sectors);

        if (!$collection->contains($request->sector_id)) {
            if (!$request->sub_category_id) {
                Flash::error('sub category required');
                return redirect()->back()->withInput();
            }
        }

        $this->productRepository->pushCriteria(new ProductsOfUserCriteria(auth()->id()));
        $product = $this->productRepository->findWithoutFail($id);

        if (empty($product)) {
            Flash::error('Product not found');
            return redirect(route('products.index'));
        }

        $input = $request->all();

        if ($input['price'] < $input['discount_price']) {
            Flash::error('Discount Price Should be less than Price Amount');
            return redirect()->back()->withInput();

        }

        $customFields = $this->customFieldRepository->findByField(
            'custom_field_model', $this->productRepository->model());

        try {
            $product->days()->detach();
            $product = $this->productRepository->update($input, $id);

            if($product->variantProducts()->exists())
            {
                $product->product_type = Product::VARIANT_BASE_PRODUCT;
            }
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($product, 'image');
                }
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $product->customFieldsValues()
                    ->updateOrCreate([
                        'custom_field_id' => $value['custom_field_id']
                    ], $value);
            }

            $parentID = $product->id;
            $days = $request->input('days');
            $attributeIDs = $request->input('attributes');

            $owletoCommissionPercent = $request->owleto_commission_percentage;
            if($owletoCommissionPercent) {

                $price = $request->discount_price > 0
                    ? $request->discount_price
                    :  $request->price;
                $taxPercentage = $request->tax;
                $tdsPercentage = Product::TDS_PERCENTAGE;
                $tcsPercentage = Product::TCS_PERCENTAGE;
                $priceExcludingtax = ($price / (100 + $taxPercentage)) * 100;

                if ($request->sector_id == Field::RESTAURANT ||
                    $request->sector_id == Field::HOME_COOKED_FOOD) {
                    $tdsAmount = 0;
                    $tcsAmount = 0;
                } else {
                    $tdsAmount = ($tdsPercentage / 100) * $priceExcludingtax;
                    $tcsAmount = ($tcsPercentage / 100) * $priceExcludingtax;
                }


                $owletoCommissionAmount = 0;
                if ($owletoCommissionPercent > 0) {
                    $owletoCommissionAmount = ($owletoCommissionPercent / 100) * $price;
                }
                $eightyPercentageOfCommissionAmount = (18 / 100) * $owletoCommissionAmount;
                $product->owleto_commission_amount = $owletoCommissionAmount;
                $product->eighty_percentage_of_commission_amount =
                    $eightyPercentageOfCommissionAmount;

                $vendorPayment = $price -
                    $owletoCommissionAmount -
                    $tdsAmount -
                    $tdsAmount -
                    $eightyPercentageOfCommissionAmount;

                $product->price_without_gst = $priceExcludingtax;
                $product->tcs_percentage = $tcsPercentage;
                $product->tcs_amount = $tcsAmount;
                $product->tds_percentage = $tdsPercentage;
                $product->tds_amount = $tdsAmount;
                $product->vendor_payment_amount = $vendorPayment;
            }

            $product->is_variant_display_product = $request->is_variant_display_product == 0 ? 0 : 1;

            if ($request->input('sector_id') != 1 || $product->sector_id != 1) {
                $product->scheduled_delivery = false;
            }

            if(!request()->user()->hasRole('admin')){
                $product->is_approved = false;
            }
            $product->save();

            if ($product->product_type == Product::VARIANT_BASE_PRODUCT) {

                $products = Product::where('parent_id', $product->id)->get();
                foreach ($products as $key => $product) {

                    if($key==0){
                        $product->is_variant_display_product = $request->is_variant_display_product == 0 ? 0 : 1;
                    }

                    $product->base_name = $request->base_name;
                    $product->tax = $request->tax;
                    $product->capacity = $request->capacity;
                    $product->market_id = $request->market_id;
                    $product->sector_id = $request->sector_id;
                    $product->category_id = $request->category_id;
                    $product->sub_category_id = $request->sub_category_id;
                    $product->owleto_commission_percentage = $request->owleto_commission_percentage;
                    $product->deliverable = $request->deliverable;
                    $product->is_enabled = $request->is_enabled;
                    $product->is_refund_or_replace = $request->is_refund_or_replace;
                    $product->return_days = $request->return_days;
                    $product->food_type = $request['food_type'];

                    $owletoCommissionPercent = $request->owleto_commission_percentage;

                    if($owletoCommissionPercent) {
                        $price = $product->discount_price > 0
                            ? $product->discount_price
                            :  $product->price;
                        $taxPercentage = $request->tax;
                        $tdsPercentage = Product::TDS_PERCENTAGE;
                        $tcsPercentage = Product::TCS_PERCENTAGE;

                        $priceExcludingtax = ($price / (100 + $taxPercentage)) * 100;

                        if ($request->sector_id == Field::RESTAURANT ||
                            $request->sector_id == Field::HOME_COOKED_FOOD) {
                            $tdsAmount = 0;
                            $tcsAmount = 0;
                        } else {
                            $tdsAmount = ($tdsPercentage / 100) * $priceExcludingtax;
                            $tcsAmount = ($tcsPercentage / 100) * $priceExcludingtax;
                        }

                        $owletoCommissionAmount = ($owletoCommissionPercent / 100) * $price;
                        $eightyPercentageOfCommissionAmount = (18 / 100) * $owletoCommissionAmount;
                        $vendorPayment = $price - $owletoCommissionAmount - $tdsAmount - $tdsAmount - $eightyPercentageOfCommissionAmount;

                        $product->price_without_gst = $priceExcludingtax;
                        $product->tcs_percentage = $tcsPercentage;
                        $product->tcs_amount = $tcsAmount;
                        $product->tds_percentage = $tdsPercentage;
                        $product->tds_amount = $tdsAmount;
                        $product->owleto_commission_amount = $owletoCommissionAmount;
                        $product->eighty_percentage_of_commission_amount =
                            $eightyPercentageOfCommissionAmount;
                        $product->vendor_payment_amount = $vendorPayment;
                    }

                    if(!request()->user()->hasRole('admin')){
                        $product->is_approved = false;
                    }
                    $product->save();
                }
            }

            $productAddons = ProductAddon::where('product_id', $id)->get();
            foreach ($productAddons as $productAddon) {
                $productAddon->delete();
            }

            if ($request->addon_name) {
                $addonNames = $request->addon_name;
                $addonPrices = $request->add_on_price;
                $addonLength = count($request->addon_name);

                for ($i = 0; $i < $addonLength; $i++) {
                    if ($addonNames[$i]) {
                        $productAddon = new ProductAddon();
                        $productAddon->product_id = $id;
                        $productAddon->name = $addonNames[$i];
                        $productAddon->price = $addonPrices[$i];
                        $product->is_refund_or_replace = $request->is_refund_or_replace;
                        $productAddon->save();
                    }
                }
            }

            if ($request->variant_product == Product::VARIANT_PRODUCT_AVAILABLE) {
                $index = 0;
                $variantProductPrices = $request->input('variant_product_price');
                $variantNames = $request->input('variant_product_name');
                $variantProductStocks = $request->input('variant_product_stock');
                $variantProductDiscountPrices = $request->input('variant_product_discount_price');
                if ($variantProductPrices != NULL) {
                    foreach ($variantProductPrices as $variantProductPrice) {
                        $variantProduct = new Product();
                        $variantProduct->base_name = $request->input('base_name');
                        $variantProduct->variant_name = $variantNames[$index];
                        $variantProduct->stock = $variantProductStocks[$index];
                        $variantProduct->price = $variantProductPrice;
                        $variantProduct->discount_price = $variantProductDiscountPrices[$index];
                        $variantProduct->capacity = $request->input('capacity');
                        $variantProduct->package_items_count = $request->input('package_items_count');
                        $variantProduct->unit = $request->input('unit');
                        $variantProduct->minimum_orders = $request->minimum_orders ?? 0;
                        $variantProduct->featured = false;
                        $variantProduct->deliverable = $request->input('deliverable');
                        $variantProduct->market_id = $request->input('market_id');
                        $variantProduct->category_id = $request->input('category_id');
                        $variantProduct->is_base_product = Product::NOT_BASE_PRODUCT;
                        $variantProduct->product_type = Product::VARIANT_PRODUCT;
                        $variantProduct->parent_id = $parentID;
                        $variantProduct->sector_id = $request->input('sector_id');
                        $variantProduct->is_enabled = $request->input('is_enabled');
                        $variantProduct->tax = $request->input('tax');
                        $variantProduct->is_refund_or_replace = $request->is_refund_or_replace;
                        $variantProduct->return_days = $request->return_days;
                        $owletoCommissionPercent = $request->owleto_commission_percentage;

                        if($owletoCommissionPercent) {
                            $price = $variantProductDiscountPrices[$index] > 0
                                ? $variantProductDiscountPrices[$index]
                                :  $variantProductPrice;
                            $taxPercentage = $request->tax;
                            $tdsPercentage = Product::TDS_PERCENTAGE;
                            $tcsPercentage = Product::TCS_PERCENTAGE;

                            $priceExcludingtax = ($price / (100 + $taxPercentage)) * 100;

                            $tdsAmount = ($tdsPercentage / 100) * $priceExcludingtax;
                            $tcsAmount = ($tcsPercentage / 100) * $priceExcludingtax;
                            $owletoCommissionAmount = ($owletoCommissionPercent / 100) * $price;
                            $eightyPercentageOfCommissionAmount = (18 / 100) * $owletoCommissionAmount;
                            $vendorPayment = $price - $owletoCommissionAmount - $tdsAmount - $tdsAmount - $eightyPercentageOfCommissionAmount;

                            $variantProduct->price_without_gst = $priceExcludingtax;
                            $variantProduct->tcs_percentage = $tcsPercentage;
                            $variantProduct->tcs_amount = $tcsAmount;
                            $variantProduct->tds_percentage = $tdsPercentage;
                            $variantProduct->tds_amount = $tdsAmount;
                            $variantProduct->owleto_commission_amount = $owletoCommissionAmount;
                            $variantProduct->eighty_percentage_of_commission_amount =
                                $eightyPercentageOfCommissionAmount;
                            $variantProduct->vendor_payment_amount = $vendorPayment;
                        }

                        $variantProduct->save();

                        if ($request->input('scheduled_delivery') == 1) {
                            $variantProduct->scheduled_delivery = $request->input('scheduled_delivery');
                            $variantProduct->order_start_time = $request->input('order_start_time');
                            $variantProduct->order_end_time = $request->input('order_end_time');
                            $variantProduct->delivery_time_id = $request->input('delivery_time_id');
                            $variantProduct->save();
                            $variantProduct->days()->detach();
                            if ($days != NULL) {
                                foreach ($days as $day) {

                                    $variantProduct->days()->attach($day, ['product_id' => $variantProduct->id]);
                                }
                            }
                        }

                        $mediaItem = $product->getMedia('image')->first();
                        if ($mediaItem) {
                            $mediaItem->copy($variantProduct, 'image');
                        }

                        $option = 0;
                        foreach ($attributeIDs as $attributeID) {

                            $attributeOptionIds = $request->input('attribute-option-' . $option);

                            $productAttributeOption = new ProductAttributeOption();
                            $productAttributeOption->product_id = $variantProduct->id;
                            $productAttributeOption->attribute_id = $attributeID;

                            $productAttributeOption->attribute_option_id = $attributeOptionIds[$index];
                            $productAttributeOption->base_product_id = $parentID;
                            $productAttributeOption->save();
                            $option++;
                        }
                        $index++;
                    }
                }
            }
            if ($request->change_attribute_option == 1) {
                $option = 0;
                $attributeIDs = $request->attributeIds;

                foreach ($attributeIDs as $attributeID) {
                    $variantProduct = ProductAttributeOption::where('product_id', $id)->where('attribute_id', $attributeID)->first();
                    $attributeOptionId = $request->input('attribute-option-' . $option);
                    if ($variantProduct and $attributeOptionId) {
                        $variantProduct->attribute_option_id = $attributeOptionId;
                        $variantProduct->save();
                    }
                    $option++;
                }
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }
        info("UPID PRODUCT : " . $product);



        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.product')]));

        return redirect(route('products.index'));
    }


    /**
     * Remove the specified Product from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        if (!env('APP_DEMO', false)) {
            $this->productRepository->pushCriteria(new ProductsOfUserCriteria(auth()->id()));
            $product = $this->productRepository->findWithoutFail($id);

            if (empty($product)) {
                Flash::error('Product not found');

                return redirect(route('products.index'));
            }

            $productAttributeOptions = ProductAttributeOption::where('product_id', $id)->delete();
            $baseProductAttributeOptions = ProductAttributeOption::where('base_product_id', $id)->delete();
            $product = Product::where('parent_id', $id)->delete();

            $this->productRepository->delete($id);

            Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.product')]));

        } else {
            Flash::warning('This is only demo app you can\'t change this section ');
        }
        return redirect(route('products.index'));
    }

    /**
     * Remove Media of Product
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $product = $this->productRepository->findWithoutFail($input['id']);
        try {
            if ($product->hasMedia($input['collection'])) {
                $product->getFirstMedia($input['collection'], ['uuid' => $input['uuid']])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function getAttributeOptions(Request $request)
    {
        if ($request->ajax()) {

            $attributeIds = $request->attribute_ids;
            $count = count($attributeIds);
            $data = [];
            $index = 1;
            if ($count > 0) {
                foreach ($attributeIds as $attributeId) {
                    $data[$index] = AttributeOption::with('attribute')->where("attribute_id", $attributeId)
                        ->get();
                    $index++;
                }

                return view('products.variants', compact('data'))->render();
            }
        }
    }

    public function market_sector(Request $request)
    {

        $sectorId = $request->id;
        $sector = Field::findOrFail($request->id);
        $sectors = $sector->fields()->pluck('market_fields.field_id')->toArray();

        if (in_array(1, $sectors)) {
            return json_encode(true);
        } else {
            return json_encode(false);
        }

    }

    public function productMarketSectors(Request $request)
    {

        $marketId = $request->id;
        $market = $this->marketRepository->findWithoutFail($marketId);
        $fields = $market->fields()->pluck('market_fields.field_id')->toArray();
        $primary_sector_id = $market->primary_sector_id;

        array_push($fields, $primary_sector_id);
        $marketsectors = Field::whereIn('id', $fields)->get();

        return json_encode($marketsectors);

    }


    public function productApproved(Request $request, $id)
    {

        $product = Product::findOrFail($id);

        $product->is_approved = $request->approve;
        $product->save();
        return response()->json($product, 200);
    }

    public function productFlashSaleApproved(Request $request, $id)
    {

        $product = Product::findOrFail($id);

        $product->is_flash_sale_approved = $request->approve;
        $product->save();
        return response()->json($product, 200);
    }

    public function addToFeatured(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $product->featured = true;
        $product->save();

        return response()->json(true, 200);
    }

    public function removeFromFeatured(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $product->featured = false;
        $product->save();

        return response()->json(true, 200);
    }
}

