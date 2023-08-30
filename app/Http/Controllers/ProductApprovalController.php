<?php
namespace App\Http\Controllers;

use App\Criteria\Products\ProductsOfUserCriteria;
use App\DataTables\ProductApprovalDataTable;
use App\Models\Field;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Flash;

class ProductApprovalController extends Controller
{

    /** @var  ProductRepository */
    private $productRepository;

    public function __construct(ProductRepository $productRepo)
    {
        parent::__construct();
        $this->productRepository = $productRepo;
    }
    /**
     * Display a listing of the Product.
     *
     * @param ProductApprovalDataTable $productDataTable
     * @return Response
     */
    public function index(ProductApprovalDataTable $productDataTable)
    {
        return $productDataTable->render('products-approvals.index');
    }

    public function show($id)
    {
        $this->productRepository->pushCriteria(new ProductsOfUserCriteria(auth()->id()));
        $product = $this->productRepository->findWithoutFail($id);

        if (empty($product)) {
            Flash::error('Product not found');

            return redirect(route('products-approvals.index'));
        }

        return view('products-approvals.show')->with('product', $product);
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);

        if (empty($product)) {
            Flash::error('Product not found');
            return redirect(route('products-approvals.index'));
        }

        return view('products-approvals.edit')->with('product', $product);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'tax' => 'required',
            'owleto_commission_percentage' => 'required',
        ];

        $request->validate($rules);
        $product = $this->productRepository->findWithoutFail($id);
        $owletoCommissionPercent = $request->owleto_commission_percentage;

        $price = $product->discount_price ?? $product->price;
        $taxPercentage = $request->tax;

        $priceExcludingtax = ($price / (100+$taxPercentage)) * 100;

        if ($request->sector_id == Field::RESTAURANT ||
            $request->sector_id == Field::HOME_COOKED_FOOD) {
            $tdsPercentage = 0;
            $tcsPercentage = 0;
            $tdsAmount = 0;
            $tcsAmount = 0;
        } else {
            $tdsPercentage = Product::TDS_PERCENTAGE;
            $tcsPercentage = Product::TCS_PERCENTAGE;
            $tdsAmount = ($tdsPercentage / 100) * $priceExcludingtax;
            $tcsAmount = ($tcsPercentage / 100) * $priceExcludingtax;
        }

        $owletoCommissionAmount = ($owletoCommissionPercent / 100) * $price;
        $eightyPercentageOfCommissionAmount = (18/100)*$owletoCommissionAmount;
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
        $product->tax = $request->tax;
        $product->owleto_commission_percentage = $request->owleto_commission_percentage;
        $product->owleto_commission_amount = $owletoCommissionAmount;
        $product->save();

        if (empty($product)) {
            Flash::error('Product not found');
            return redirect(route('product-approvals.index'));
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.product')]));

        return redirect(route('product-approvals.index'));
    }
}

