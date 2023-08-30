<?php
/**
 * File name: ProductReviewDataTable.php
 * Last modified: 2020.05.04 at 09:04:19
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\DataTables;

use App\Criteria\ProductReviews\OrderProductReviewsOfUserCriteria;
use App\Criteria\ProductReviews\ProductReviewsOfUserCriteria;
use App\Models\CustomField;
use App\Models\Market;
use App\Models\ProductReview;
use App\Models\User;
use App\Repositories\ProductReviewRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class ProductReviewDataTable extends DataTable
{
    /**
     * custom fields columns
     * @var array
     */
    public static $customFields = [];
    private $productReviewRepo;
    private $myReviews;


    public function __construct(ProductReviewRepository $productReviewRepo)
    {
        $this->productReviewRepo = $productReviewRepo;
        $this->myReviews = $this->productReviewRepo->getByCriteria(new ProductReviewsOfUserCriteria(auth()->id()))->pluck('id')->toArray();
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);
        $columns = array_column($this->getColumns(), 'data');
        $dataTable = $dataTable
            ->editColumn('updated_at', function ($product_review) {
                return getDateColumn($product_review, 'updated_at');
            })
            ->addColumn('action', function ($product_review) {
                return view('product_reviews.datatables_actions', ['id' => $product_review->id, 'myReviews' => $this->myReviews])->render();
            })
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ProductReview $model
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function query(ProductReview $model)
    {
        $user = User::findOrFail(Auth::id());

        if (auth()->user()->hasRole('admin')) {

            $user = auth()->user();

            return $model->with("user")
                ->with("product")
                ->join("products", "products.id", "=", "product_reviews.product_id")
                ->join("markets", "markets.id", "=", "products.market_id")
                ->where("markets.city_id", $user->city_id)
                ->select('product_reviews.*')
                ->newQuery();

        } else if (auth()->user()->hasRole('vendor_owner')) {

            $userMarketIds = Market::whereHas('users', function ($query){
                $query->where('user_id', Auth::id());
            })->pluck('id');

            return $model->with("user")
                ->with("product")
                ->join("products", "products.id", "=", "product_reviews.product_id")
                ->join("markets", "markets.id", "=", "products.market_id")
                ->join("user_markets", "user_markets.market_id", "=", "products.market_id")
                ->where("markets.city_id", $user->city_id)
                ->whereIn("markets.id", $userMarketIds)
                ->select('product_reviews.*')
                ->newQuery();
        }
        else{
            return $model->with("user")
                ->with("product")
                ->join("products", "products.id", "=", "product_reviews.product_id")
                ->join("markets", "markets.id", "=", "products.market_id")
                ->where("markets.city_id", $user->city_id)
                ->select('product_reviews.*')
                ->newQuery();
        }

    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->addAction(['title'=>trans('lang.actions'),'width' => '80px', 'printable' => false, 'responsivePriority' => '100'])
            ->parameters([
                'searching' => true,
                'ordering' => false,
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $columns = [
            [
                'data' => 'review',
                'title' => trans('lang.product_review_review'),

            ],
            [
                'data' => 'rate',
                'title' => trans('lang.product_review_rate'),

            ],
            [
                'data' => 'user.name',
                'name' => 'user.name',
                'title' => trans('lang.product_review_user_id'),

            ],
            [
                'data' => 'product.name',
                'name' => 'user.name',
                'title' => trans('lang.product_review_product_id'),

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.product_review_updated_at'),
                'searchable' => true,
            ]
        ];

        $hasCustomField = in_array(ProductReview::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', ProductReview::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.product_review_' . $field->name),
                    'orderable' => false,
                    'searchable' => false,
                ]]);
            }
        }
        return $columns;
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'product_reviewsdatatable_' . time();
    }

    /**
     * Export PDF using DOMPDF
     * @return mixed
     */
    public function pdf()
    {
        $data = $this->getDataForPrint();
        $pdf = PDF::loadView($this->printPreview, compact('data'));
        return $pdf->download($this->filename() . '.pdf');
    }
}
