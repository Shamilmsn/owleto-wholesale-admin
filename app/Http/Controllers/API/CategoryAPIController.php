<?php
/**
 * File name: CategoryAPIController.php
 * Last modified: 2020.05.04 at 09:04:18
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API;


use App\Criteria\Categories\CategoriesOfFieldsCriteria;
use App\Criteria\Categories\CategoriesOfMarketCriteria;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Market;
use App\Models\Product;
use App\Repositories\CategoryRepository;
use Flash;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class CategoryController
 * @package App\Http\Controllers\API
 */
class CategoryAPIController extends Controller
{
    /** @var  CategoryRepository */
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepo)
    {
        $this->categoryRepository = $categoryRepo;
    }

    /**
     * Display a listing of the Category.
     * GET|HEAD /categories
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $categories = Category::query()->with('market_categories')->where('parent_id', null);

        if ($request->market_id) {
            $productCategoryIds = Product::where('market_id', $request->market_id)->pluck('category_id');

            $categories = $categories->whereIn('id', $productCategoryIds);
        }

        if ($request->sector_id) {
            $marketIds = Market::where(function ($query) use ($request){
                    $query->whereHas('fields', function ($query) use ($request){
                        $query->where('id', $request->sector_id);
                    })->orWhere('primary_sector_id', $request->sector_id);
                })
                ->pluck('id');

                 $categories = $categories->whereHas('market_categories', function ($query) use ($marketIds) {
                     $query->whereIn('id', $marketIds);
                 });
        }

        $categories = $categories->get();


        return $this->sendResponse($categories->toArray(), 'Categories retrieved successfully');
    }


    public function store(Request $request){
        try{
            $categories =  $this->categoryRepository->pushCriteria(new RequestCriteria($request));
            $categories =  $this->categoryRepository->pushCriteria(new LimitOffsetCriteria($request));
            $categories =  $this->categoryRepository->pushCriteria(new CategoriesOfFieldsCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }



        if($request->market_id){
            $categories = $this->categoryRepository->whereHas('products', function ($query) use ($request){
                $query->where('market_id', $request->market_id)
                    ->where('is_approved',true);
            });
        }

        $categories = $categories->get();

        return $this->sendResponse($categories->toArray(), 'Categories retrieved successfully');
    }

    /**
     * Display the specified Category.
     * GET|HEAD /categories/{id}
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        /** @var Category $category */
        if (!empty($this->categoryRepository)) {
            $category = $this->categoryRepository->findWithoutFail($id);
        }

        if (empty($category)) {
            return $this->sendError('Category not found');
        }

        return $this->sendResponse($category->toArray(), 'Category retrieved successfully');
    }
}
