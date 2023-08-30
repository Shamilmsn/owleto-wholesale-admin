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
use App\Models\DriverReview;
use App\Models\Market;
use App\Models\ProductReview;
use App\Models\User;
use App\Repositories\ProductReviewRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class DriverReviewDataTable extends DataTable
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
            ->editColumn('updated_at', function ($driverReview) {
                return getDateColumn($driverReview, 'updated_at');
            })
            ->editColumn('driver_id', function ($driverReview) {
                return optional($driverReview->driverUser)->name;
            })
            ->addColumn('user', function ($driverReview) {
                return optional($driverReview->user)->name;
            })
            ->rawColumns(array_merge($columns));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\DriverReview $model
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function query(DriverReview $model)
    {
        return $model->with("user")
            ->with("driver.user")
            ->select('driver_reviews.*')
            ->newQuery();

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
            ->parameters([
                'searching' => false,
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
                'data' => 'rating',
                'title' => trans('lang.product_review_rate'),

            ],
            [
                'data' => 'order_id',
                'title' => 'OrderId',

            ],
            [
                'data' => 'user',
                'name' => 'user',
                'title' => trans('lang.product_review_user_id'),

            ],
            [
                'data' => 'driver_id',
                'name' => 'driver_id',
                'title' => 'Driver',

            ],
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
