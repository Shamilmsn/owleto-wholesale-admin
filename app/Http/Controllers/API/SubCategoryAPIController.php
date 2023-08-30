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
class SubCategoryAPIController extends Controller
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
        $this->validate($request, [
            'category_id' => 'required',
//            'market_id' => 'required',
//            'field_id' => 'required',
        ]);

        $productsSubCategoryIds = Product::where('category_id', $request->category_id)
            ->where(function ($query) use ($request) {
                if ($request->market_id) {
                    $query->where('market_id', $request->market_id);
                }
            })
            ->whereNotNull('sub_category_id')
            ->pluck('sub_category_id');

        $categories = Category::whereIn('id', $productsSubCategoryIds)->get();



//        try{
//            $categories =  $this->categoryRepository->pushCriteria(new CategoriesOfFieldsCriteria($request));
//        } catch (RepositoryException $e) {
//            return $this->sendError($e->getMessage());
//        }
//        if($request->sector_id){
//            $categories = $this->categoryRepository->where('field_id', $request->sector_id);
//        }
//
//        if($request->market_id){
//            $categories = $this->categoryRepository->pushCriteria(new CategoriesOfMarketCriteria($request));
//        }
//
//        $categories = $categories->where('parent_id', $request->category_id)->whereNotNull('field_id')->get();

        return $this->sendResponse($categories->toArray(), 'Categories retrieved successfully');
    }


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
