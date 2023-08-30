<?php
/**
 * File name: CategoryDataTable.php
 * Last modified: 2020.04.30 at 08:21:08
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\DataTables;

use App\Models\OrderRequest;
use App\Models\TemporaryOrderRequest;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class TemporaryOrderRequestDatatable extends DataTable
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
            ->editColumn('image', function ($tempOrderReqest) {

                $src = url("storage/order-requests/bill-images/$tempOrderReqest->bill_image");
                return '<img src="'.$src.'" width="100" height="100">';
            })
//            ->editColumn('updated_at', function ($category) {
//                return getDateColumn($category, 'updated_at');
//            })
            ->addColumn('action', 'order_requests.temp_order_request_actions')
            ->rawColumns(array_merge($columns,['action']));

        return $dataTable;
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
                'data' => 'net_amount',
                'title' => trans('lang.temporary_order_request_net_amount'),
                'orderable' => false,

            ],
            [
                'data' => 'distance',
                'name' => 'distance',
                'title' => trans('lang.distance'),
            ],
            [
                'data' => 'image',
                'title' => trans('lang.category_image'),
                'orderable' => false,
               // 'searchable' => false, 'orderable' => false, 'exportable' => false, 'printable' => false,
            ],

        ];

        return $columns;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\TemporaryOrderRequest $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(TemporaryOrderRequest $model)
    {
        return $model->newQuery()
            ->where('order_request_id', $this->id)
            ->orderBy('id','desc')->select('temporary_order_requests.*');
//        return $model->newQuery();
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
     * Export PDF using DOMPDF
     * @return mixed
     */
    public function pdf()
    {
        $data = $this->getDataForPrint();
        $pdf = PDF::loadView($this->printPreview, compact('data'));
        return $pdf->download($this->filename() . '.pdf');
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'temp_order_request_datatable_' . time();
    }
}
