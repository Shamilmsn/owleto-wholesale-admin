<?php

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\OrderRequest;
use App\Models\PackageOrder;
use App\Models\PickUpDeliveryOrder;
use App\Models\ProductOrderRequestOrder;
use App\Models\TemporaryOrderRequest;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class PickUpDeliveryOrderDetailDataTable extends DataTable
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

            ->editColumn('updated_at', function ($pickUpOrder) {
                return getDateColumn($pickUpOrder, 'updated_at');
            })
            ->rawColumns(array_merge($columns));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\PickUpDeliveryOrder $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(PickUpDeliveryOrder $model)
    {
        $query = $model->newQuery()
            ->with('pickUpDeliveryOrderRequest')
            ->where('order_id', $this->id)
            ->select('pick_up_delivery_orders.*')
            ->orderBy('pick_up_delivery_orders.id', 'desc');

        return $query;

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
                'data' => 'price',
                'title' => trans('lang.package_order_price'),
                'orderable' => false,

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.pickup_order_updated_at'),
                'orderable' => false,

            ],

        ];

//        $hasCustomField = in_array(ProductOrder::class, setting('custom_field_models', []));
//        if ($hasCustomField) {
//            $customFieldsCollection = CustomField::where('custom_field_model', ProductOrder::class)->where('in_table', '=', true)->get();
//            foreach ($customFieldsCollection as $key => $field) {
//                array_splice($columns, $field->order - 1, 0, [[
//                    'data' => 'custom_fields.' . $field->name . '.view',
//                    'title' => trans('lang.product_order_' . $field->name),
//                    'orderable' => false,
//                    'searchable' => false,
//                ]]);
//            }
//        }
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