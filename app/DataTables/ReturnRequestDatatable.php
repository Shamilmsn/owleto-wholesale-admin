<?php
/**
 * File name: OrderDataTable.php
 * Last modified: 2020.04.30 at 08:21:08
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\DataTables;

use App\Models\PackageOrder;
use App\Models\ReturnRequest;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class ReturnRequestDatatable extends DataTable
{
    /**
     * custom fields columns
     * @var array
     */
    public static $customFields = [];

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);
        $columns = array_column($this->getColumns(), 'data');
        $dataTable = $dataTable
            ->editColumn('user_id', function ($returnRequest) {
                return $returnRequest->user->name;
            })
            ->editColumn('product_id', function ($returnRequest) {
                return $returnRequest->product->name;
            })
            ->rawColumns(array_merge($columns));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ReturnRequest $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ReturnRequest $model)
    {
        return $model->newQuery()
            ->with("user")
            ->with("product")
            ->select('return_requests.*');

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
//            ->addAction(['title'=>trans('lang.actions'),'width' => '80px', 'printable' => false, 'responsivePriority' => '100'])
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
                'data' => 'user_id',
                'name' => 'user_id',
                'title' => 'User',
                'searchable' => true,
            ],
            [
                'data' => 'order_id',
                'title' => "orderId",
                'orderable' => false,
            ],
            [
                'data' => 'amount',
                'title' => "Amount",
                'orderable' => false,

            ],
            [
                'data' => 'product_id',
                'title' => 'Product',
                'orderable' => true,

            ],
            [
                'data' => 'description',
                'title' => 'Description',
                'orderable' => true,
            ],

        ];

        return $columns;
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'package_ordersdatatable_' . time();
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
