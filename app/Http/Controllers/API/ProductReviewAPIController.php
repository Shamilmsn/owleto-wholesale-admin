<?php

namespace App\Http\Controllers\API;


use App\Models\ProductReview;
use App\Models\ProductReviewImage;
use App\Repositories\ProductReviewRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Illuminate\Support\Facades\Response;
use Prettus\Repository\Exceptions\RepositoryException;
use Flash;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class ProductReviewController
 * @package App\Http\Controllers\API
 */

class ProductReviewAPIController extends Controller
{
    /** @var  ProductReviewRepository */
    private $productReviewRepository;

    public function __construct(ProductReviewRepository $productReviewRepo)
    {
        $this->productReviewRepository = $productReviewRepo;
    }

    /**
     * Display a listing of the ProductReview.
     * GET|HEAD /productReviews
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->productReviewRepository->skipCache();

        try{
            $this->productReviewRepository->pushCriteria(new RequestCriteria($request));
            $this->productReviewRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $productReviews = ProductReview::all();

        return $this->sendResponse($productReviews->toArray(), 'Product Reviews retrieved successfully');
    }

    /**
     * Display the specified ProductReview.
     * GET|HEAD /productReviews/{id}
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        /** @var ProductReview $productReview */
        if (!empty($this->productReviewRepository)) {
            $productReview = $this->productReviewRepository->findWithoutFail($id);
        }

        if (empty($productReview)) {
            return $this->sendError('Product Review not found');
        }

        return $this->sendResponse($productReview->toArray(), 'Product Review retrieved successfully');
    }

    /**
     * Store a newly created ProductReview in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'product_id' => 'required',
            'rate' => 'required',
        ]);

        try {
            $productReview = ProductReview::where('product_id',$request->product_id)
                ->where('user_id',$request->user_id)
                ->first();

            if(!$productReview) {
                $productReview = new ProductReview();
            }
            
            $productReview->review = $request->review;
            $productReview->rate = $request->rate;
            $productReview->user_id = $request->user_id;
            $productReview->product_id = $request->product_id;
            $productReview->save();
            if($request->images)
            {
                foreach ($request->images as $file) {
                    $filename = time() .Str::random(8). '.' . $file->getClientOriginalExtension();
                    $file->storeAs('public/productReviews/', $filename);
                    $productReviewImage = new ProductReviewImage();
                    $productReviewImage->product_review_id = $productReview->id;
                    $productReviewImage->image = $filename;
                    $productReviewImage->save();
                }

            }
        } catch (ValidatorException $e) {
            return $this->sendError('Product Review not found');
        }

        return $this->sendResponse($productReview->toArray(),__('lang.saved_successfully',['operator' => __('lang.product_review')]));
    }
}
