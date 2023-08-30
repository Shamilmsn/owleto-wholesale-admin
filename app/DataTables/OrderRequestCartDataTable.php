<?php

namespace App\DataTables;

use App\Models\Cart;
use App\Models\CustomField;
use App\Models\OrderRequest;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class OrderRequestCartDataTable extends DataTable
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
            ->editColumn('product_id',function($cart){
                if(!$cart->product_id){
                    return '-';
                }
                return $cart->product->name;
            })
            ->editColumn('price',function($cart){

                return $cart->product->price;
            })
            ->editColumn('net_amount',function($cart){

                return $cart->product->price * $cart->quantity;
            })
            ->addColumn('action', 'order_requests.cart_action');
//            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Cart $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Cart $model)
    {
        $orderRequest = OrderRequest::findOrFail($this->id);

        return $model->newQuery()
            ->with('product')
            ->where('user_id', $orderRequest->user_id)
            ->orderBy('id','desc');
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
            ->addAction(['title'=>trans('lang.actions'),'width' => '80px', 'printable' => false ,'responsivePriority'=>'100'])
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
                'data' => 'product_id',
                'title' => trans('lang.cart_product_id'),
            ],
            [
                'data' => 'quantity',
                'title' => trans('lang.cart_quantity'),
            ],
            [
                'data' => 'price',
                'title' => trans('lang.product_order_price'),
            ],
            [
                'data' => 'net_amount',
                'title' => trans('lang.net_amount'),
            ],
//            [
//                'data' => 'updated_at',
//                'title' => trans('lang.cart_updated_at'),
//                'searchable' => false,
//            ]

        ];

        $hasCustomField = in_array(OrderRequest::class, setting('custom_field_models',[]));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', OrderRequest::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.order_status_' . $field->name),
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
        return 'order_statusesdatatable_' . time();
    }

    /**
     * Export PDF using DOMPDF
     * @return mixed
     */
    public function pdf()
    {
        $data = $this->getDataForPrint();
        $pdf = PDF::loadView($this->printPreview, compact('data'));
        return $pdf->download($this->filename().'.pdf');
    }
}