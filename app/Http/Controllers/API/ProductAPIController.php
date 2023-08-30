<?php
/**
 * File name: ProductAPIController.php
 * Last modified: 2020.05.04 at 09:04:19
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API;


use App\Criteria\Products\MarketProductCriteria;
use App\Criteria\Products\NearCriteria;
use App\Criteria\Products\ProductsOfCategoriesCriteria;
use App\Criteria\Products\ProductsOfFieldsCriteria;
use App\Criteria\Products\TrendingWeekCriteria;
use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductAttributeOption;
use App\Repositories\CustomFieldRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UploadRepository;
use Carbon\Carbon;
use Flash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class ProductController
 * @package App\Http\Controllers\API
 */
class ProductAPIController extends Controller
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


    public function __construct(ProductRepository $productRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo)
    {
        parent::__construct();
        $this->productRepository = $productRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
//        $this->productRepository->skipCache();
    }

    /**
     * Display a listing of the Product.
     * GET|HEAD /products
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {

        try {
            $this->productRepository->pushCriteria(new RequestCriteria($request));
            $this->productRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->productRepository->pushCriteria(new ProductsOfFieldsCriteria($request));
            //  $this->productRepository->pushCriteria(new BaseProductCriteria($request));
            if ($request->get('market_id')) {

                $this->productRepository->pushCriteria(new MarketProductCriteria($request));
            }
            if ($request->get('trending', null) == 'week') {
                $this->productRepository->pushCriteria(new TrendingWeekCriteria($request));
            } else {
                $products = $this->productRepository->pushCriteria(new NearCriteria($request));
            }

            if ($request->sector_id) {
                $products = $this->productRepository->where('sector_id', $request->sector_id);
            }

            if ($request->Is_flash_sale_product) {

                $now = Carbon::now()->format('Y-m-d H:i:s');

                $products = $this->productRepository
                    ->whereHas('market', function ($query) {})
                    ->where('is_enabled', true)
                    ->where('deliverable', 1)
                    ->where('is_approved', true)
                    ->where('is_flash_sale', true)
                    ->where('flash_sale_start_time', '<=', $now)
                    ->where('flash_sale_end_time', '>=', $now)
                    ->where('is_flash_sale_approved', true)
                    ->get();
            } else {

                $products = $this->productRepository
                    ->whereHas('market', function ($query) {})
                    ->where('is_enabled', true)
                    ->where('deliverable', 1)
                    ->where('is_approved', true)
                    ->where('product_type', '!=', Product::VARIANT_BASE_PRODUCT)
                    ->Where(function ($query) {
                        $query->where('is_variant_display_product', true)
//                            ->orWhere('product_type',Product::VARIANT_BASE_PRODUCT);
                        ->orWhere('product_type',Product::STANDARD_PRODUCT);
                    })
                    ->orderByDesc('id')
                    ->get();
            }

        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($products->toArray(), 'Products retrieved successfully');
    }

    public function autocomplete(Request $request)
    {
        try {
            $products = DB::table('products')->select('id')
                ->selectRaw('CONCAT(COALESCE(base_name, ""), " ", COALESCE(variant_name, "")) AS name')
                ->where(function ($query) use ($request) {
                    if ($request->get('query')) {
                        $query->where('base_name', 'LIKE', '%' . $request->get('query') . '%')
                            ->orWhere('base_name', 'LIKE', '%' . $request->get('query') . '%');
                    }
                })
                ->paginate(10);
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($products->toArray(), 'Products autocomplete successfully');
    }

    /**
     * Display a listing of the Product.
     * GET|HEAD /products/categories
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function categories(Request $request)
    {
//        $this->productRepository->skipCache();

        try {
            $this->productRepository->pushCriteria(new RequestCriteria($request));
            $this->productRepository->pushCriteria(new LimitOffsetCriteria($request));

            $products = $this->productRepository->pushCriteria(new ProductsOfFieldsCriteria($request));

            $products = $this->productRepository->pushCriteria(new ProductsOfCategoriesCriteria($request));

            $products = $this->productRepository
                ->where('is_enabled', true)
                ->where('deliverable', 1)
                ->where('is_approved', true)
                ->where('product_type', '!=', Product::VARIANT_BASE_PRODUCT)
                ->Where(function ($query) {
                    $query->where('is_variant_display_product', true)
                        ->orWhere('product_type',Product::STANDARD_PRODUCT);
                });

            if ($request->search_name) {
                $products = $products->where('base_name', 'like', '%' . $request->search_name . '%')
                    ->where('base_name', 'like', '%' . $request->search_name . '%')
                    ->orWhere('variant_name', 'like', '%' . $request->search_name . '%');
            }
//
            if ($request->sector_id) {
                $products = $products->where('sector_id', $request->sector_id);
            }

            if ($request->market_id) {
                $products = $products->where('market_id', $request->market_id);
            }

            if ($request->Is_flash_sale_product) {

                $now = Carbon::now()->format('Y-m-d H:i:s');
                $products = $products
                    ->where('is_flash_sale_approved', true)
                    ->where('is_flash_sale', true)
                    ->where('flash_sale_start_time', '<=', $now)
                    ->where('flash_sale_end_time', '>=', $now);

            }

            if ($request->featured == 1) {
                $products = $products->where('featured', true);
            }

            $products = $products->get();

        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($products->toArray(), 'Products retrieved successfully');
    }

    /**
     * Display the specified Product.
     * GET|HEAD /products/{id}
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        /** @var Product $product */
        if (!empty($this->productRepository)) {
            try {
                $this->productRepository->pushCriteria(new RequestCriteria($request));
                $this->productRepository->pushCriteria(new LimitOffsetCriteria($request));
            } catch (RepositoryException $e) {
                return $this->sendError($e->getMessage());
            }

            $productRow = $this->productRepository->findWithoutFail($id);

            if ($productRow->product_type == 1 || $productRow->parent_id == NULL) {

                $product = $this->productRepository->findWithoutFail($id);

            } else {

                $baseProductId = $productRow->parent_id;

                $product = $productRow->with('market', 'productAttributeOptions.attribute', 'productAttributeOptions.attributeOption', 'addons')
                    ->WhereHas('productAttributeOptions', function ($query) use ($baseProductId, $id) {
                        $query->where('base_product_id', $baseProductId)
                            ->where('product_id', $id);
                    })
                    ->first();

            }

        }

        if (empty($product)) {
            return $this->sendError('Product not found');
        }

        return $this->sendResponse($product->toArray(), 'Product retrieved successfully');
    }

    /**
     * Store a newly created Product in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->productRepository->model());
        try {
            $product = $this->productRepository->create($input);
            $product->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($product, 'image');
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($product->toArray(), __('lang.saved_successfully', ['operator' => __('lang.product')]));
    }

    /**
     * Update the specified Product in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function update($id, Request $request)
    {
        $product = $this->productRepository->findWithoutFail($id);

        if (empty($product)) {
            return $this->sendError('Product not found');
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->productRepository->model());
        try {
            $product = $this->productRepository->update($input, $id);

            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($product, 'image');
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $product->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($product->toArray(), __('lang.updated_successfully', ['operator' => __('lang.product')]));

    }

    /**
     * Remove the specified Product from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $product = $this->productRepository->findWithoutFail($id);

        if (empty($product)) {
            return $this->sendError('Product not found');
        }

        $product = $this->productRepository->delete($id);

        return $this->sendResponse($product, __('lang.deleted_successfully', ['operator' => __('lang.product')]));

    }


    public function variantAttributeOption(Request $request, $id)
    {

        $baseProductId = $request->id;

        $attributeOptions = ProductAttributeOption::with(['attribute', 'attributeOption'])
            ->where('base_product_id', $baseProductId)
            ->whereHas('product', function ($query) {
                $query->where('is_enabled', true);
            })
            ->get();


        $childProductIds = $attributeOptions->pluck('product_id')->toArray();

        if (empty($attributeOptions)) {
            return $this->sendError('Product Attribute Options not found');
        }

        $productAttributeOptionsByAttributes = $attributeOptions->groupBy('attribute_id');
        $productAttributeOptionsByAttributes = $productAttributeOptionsByAttributes->map(function ($productAttributeOptions) {
            return collect($productAttributeOptions)->reduce(function ($carry, ProductAttributeOption $productAttributeOption) {

                if ($carry == null) {
                    /** @var Attribute $attribute */
                    $attribute = $productAttributeOption->attribute;
                    $carry = $attribute->toArray();
                    // info($carry);
                }

                if (!collect($carry['options'] ?? [])->contains('id', $productAttributeOption->attributeOption->id)) {
                    $carry['options'][] = $productAttributeOption->attributeOption;
                }

                return $carry;
            });
        })->values();

        $childProductIds = array_unique($childProductIds);
        $datas = [];

        foreach ($childProductIds as $key => $childProductId) {

            array_push($datas, $childProductId);

            $attributes = ProductAttributeOption::where('product_id', $childProductId)->get();
            $attributes = collect($attributes)->map(function (ProductAttributeOption $attributes) {
                return $attributes->only('attribute_id', 'attribute_option_id');
            });

            $productsAttributeOptionsMap[] = (object)['product_id' => $childProductId, 'variant_attributes' => $attributes];
        }

        return $this->sendResponse([
            'variants' => $productsAttributeOptionsMap,
            'attributes' => $productAttributeOptionsByAttributes,
        ], 'Product Attribute Options retrieved successfully');
    }
}

