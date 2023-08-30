<?php
/**
 * File name: MarketAPIController.php
 * Last modified: 2020.08.13 at 13:43:34
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API\Manager;

use App\Http\Controllers\API\Driver\ProfileAPIController;
use App\Http\Controllers\Controller;
use App\Criteria\Products\MarketProductCriteria;
use App\Models\Market;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Flash;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class MarketController
 * @package App\Http\Controllers\API
 */

class ProductAPIController extends Controller
{
    /** @var  ProductRepository */
    private $productRepository;


    public function __construct(ProductRepository $productRepository)
    {
        parent::__construct();
        $this->productRepository = $productRepository;

    }

    public function index(Request $request)
    {
        $userMarketIds = Market::whereHas('users', function ($query){
            $query->where('user_id', auth()->id());
        })->pluck('id');

        try {
            $this->productRepository->pushCriteria(new RequestCriteria($request));
            $this->productRepository->pushCriteria(new LimitOffsetCriteria($request));
//            $this->productRepository->pushCriteria(new MarketProductCriteria($request));

            $products = $this->productRepository
                ->where('product_type', '!=', Product::VARIANT_BASE_PRODUCT)
                ->where('deliverable', 1)
                ->whereIn('market_id', $userMarketIds)
                ->where('is_approved', true);

            if($request->market_id){
                $products = $this->productRepository->where('market_id', $request->market_id);
            }

            if($request->search_name){
                $products = $this->productRepository->where('base_name', 'like', '%'. $request->search_name .'%')
                    ->orWhere('variant_name', 'like', '%'. $request->search_name .'%');
            }

            $products = $products->get();


        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($products->toArray(), 'Products retrieved successfully');

    }

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

                $product = $productRow->with('market','productAttributeOptions.attribute', 'productAttributeOptions.attributeOption','addons')
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

    public function enableOrDisable(Request $request)
    {
        $product = Product::find($request->product_id);

        if(!$product){
            return $this->sendError('Product not found');
        }

        $product->is_enabled = $request->enable;
        $product->save();

        return $this->sendResponse($product, 'Product updated successfully');


    }

}
