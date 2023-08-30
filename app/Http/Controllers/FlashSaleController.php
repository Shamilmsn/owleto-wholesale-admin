<?php
/**
 * File name: ProductController.php
 * Last modified: 2020.04.30 at 08:21:08
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers;

use App\DataTables\FlashSaleProductsDataTable;
use App\DataTables\ProductDataTable;
use App\Models\Product;
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
use Flash;
use Illuminate\Http\Request;
use Prettus\Validator\Exceptions\ValidatorException;

class FlashSaleController extends Controller
{
    /** @var  ProductRepository */
    private $productRepository;

    public function __construct(ProductRepository $productRepo)
    {
        parent::__construct();
        $this->productRepository = $productRepo;
    }

    public function index(FlashSaleProductsDataTable $productDataTable)
    {
        return $productDataTable->render('flash-sales.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'flash_sale_price' => 'required|numeric|min:1',
            'flash_sale_start_time' => 'required',
            'flash_sale_end_time' => 'required',
        ]);

         try {

             $product = Product::findOrFail($request->product_id);
             if($product->is_flash_sale) {
                 Flash::error('Product already in flash sale');
                 return redirect(route('flash-sales.index'));
             }
             $product->is_flash_sale = true;
             $product->flash_sale_price = $request->flash_sale_price;
             $product->flash_sale_start_time = $request->flash_sale_start_time;
             $product->flash_sale_end_time = $request->flash_sale_end_time;
             if($request->user()->hasRole('admin')) {
                 $product->is_flash_sale_approved = true ;
             }

             $product->save();

             Flash::success(__('lang.saved_successfully',
                 ['operator' => 'Flash sale created successfully']));

             return redirect(route('flash-sales.index'));

         } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
             return redirect(route('flash-sales.index'));
        }


    }

    public function create()
    {
        $products = Product::cursor();
        return view('flash-sales.create')->with( "products", $products);
    }

    public function edit($id)
    {
        $product = $this->productRepository->findWithoutFail($id);
        $products = Product::cursor();

        return view('flash-sales.edit')
            ->with( "product", $product)
            ->with( "products", $products);
    }

    public function update( $productId, Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'flash_sale_price' => 'required|numeric|min:1',
            'flash_sale_start_time' => 'required',
            'flash_sale_end_time' => 'required',
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            $product->is_flash_sale = true;
            $product->flash_sale_price = $request->flash_sale_price;
            $product->flash_sale_start_time = $request->flash_sale_start_time;
            $product->flash_sale_end_time = $request->flash_sale_end_time;
            if($request->user()->hasRole('admin')) {
                $product->is_flash_sale_approved = true ;
            }

            $product->save();

            Flash::success(__('lang.saved_successfully',
                ['operator' => 'Flash sale updated successfully']));

            return redirect(route('flash-sales.index'));

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
            return redirect(route('flash-sales.index'));
        }

    }

    public function approveFlashSale($productId)
    {
        $product = Product::findOrFail($productId);
        $product->is_flash_sale_approved = true;
        $product->save();

        Flash::success(__('lang.saved_successfully',
            ['operator' => 'Flash sale approved successfully']));

        return redirect(route('flash-sales.index'));
    }

    public function removeFlashSale($productId)
    {
        $product = Product::findOrFail($productId);
        $product->is_flash_sale_approved = false;
        $product->is_flash_sale = false;
        $product->flash_sale_price = null;
        $product->flash_sale_start_time = null;
        $product->flash_sale_end_time = null;
        $product->save();

        Flash::success(__('lang.saved_successfully',
            ['operator' => 'Flash sale removed successfully']));

        return redirect(route('flash-sales.index'));
    }

}

