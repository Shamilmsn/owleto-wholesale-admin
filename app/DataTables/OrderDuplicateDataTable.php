<?php
/**
 * File name: OrderDataTable.php
 * Last modified: 2020.04.30 at 08:21:08
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\Order;
use App\Models\PaymentMethod;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class OrderDuplicateDataTable extends DataTable
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
        return datatables()
            ->eloquent($query)
            ->filter(function ($query) {
                if (request()->input('filter.status')) {
                    $query->where(function ($query) {
                        $query->where('order_status_id', request('filter.status'));
                    });
                }

            })
            ->editColumn('id', function ($order) {
                return "#".$order->id;
            })
            ->editColumn('updated_at', function ($order) {
                return getDateColumn($order, 'updated_at');
            })
            ->editColumn('delivery_fee', function ($order) {
                return getPriceColumn($order, 'delivery_fee');
            })
            ->editColumn('tax', function ($order) {
                return getPriceColumn($order, 'tax');
            })
            ->editColumn('order.status', function ($order) {
                return $order->orderStatus->status;
            })
            ->editColumn('delivery_type.name', function ($order) {
                if($order->deliveryType != NULL) {
                    return optional($order->deliveryType)->name;
                }else{
                    return 'Takeaway';
                }

            })
            ->editColumn('payment.method', function ($order) {
                return optional($order->paymentMethod)->name;
            })
            ->editColumn('active', function ($product) {
                return getBooleanColumn($product, 'active');
            })
            ->addColumn('action', 'orders.datatables_actions')
            ->rawColumns(array_merge($columns, ['action']));

//        return $dataTable;
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
                'data' => 'id',
                'title' => trans('lang.order_id'),

            ],
            [
                'data' => 'user.name',
                'name' => 'user.name',
                'title' => 'Customer',

            ],
            [
                'data' => 'order_status.status',
                'name' => 'orderStatus.status',
                'title' => trans('lang.order_order_status_id'),

            ],
            [
                'data' => 'tax',
                'title' => trans('lang.order_tax')."<span>" . ' '.   setting('default_currency') . "</span>",
                'searchable' => false,

            ],
            [
                'data' => 'delivery_fee',
                'title' => trans('lang.order_delivery_fee')."<span>" . ' '.   setting('default_currency') . "</span>",
                'searchable' => false,

            ],
            [
                'data' => 'payment.method',
                'name' => 'payment.method',
                'title' => trans('lang.payment_method'),

            ],
            [
                'data' => 'delivery_type.name',
                'name' => 'deliveryType.name',
                'title' => trans('lang.delivery_type'),

            ],
            [
                'data' => 'active',
                'title' => trans('lang.order_active'),

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.order_updated_at'),
                'searchable' => true,
                'orderable' => true,

            ]
        ];

        $hasCustomField = in_array(Order::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Order::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.order_' . $field->name),
                    'orderable' => false,
                    'searchable' => false,
                ]]);
            }
        }
        return $columns;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Order $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Order $model)
    {
        if (auth()->user()->hasRole('admin')) {

            $user = auth()->user();

            return Order::with('deliveryType')
                ->with("user")
                ->with("orderStatus")
                ->with("market.field")
                ->with('payment')
                ->join("markets", "markets.id", "=", "orders.market_id")
                ->where(function ($query) use ($user){
                    $query->where('type', Order::PRODUCT_TYPE)
                        ->where("markets.city_id", $user->city_id)
                        ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_RAZORPAY)
                        ->where('payment_status', 'SUCCESS');
                })
                ->orWhere(function ($query) use ($user){
                    $query->where('type', Order::PRODUCT_TYPE)
                        ->where("markets.city_id", $user->city_id)
                        ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_COD);
                })
                ->orderBy('orders.order_status_id', 'asc')
                ->select('orders.*');

        } else if (auth()->user()->hasRole('vendor_owner')) {

            return $model->newQuery()->with("user")
                ->with("orderStatus")
                ->with('payment')
                ->with("market.field")
                ->join("product_orders", "orders.id", "=", "product_orders.order_id")
                ->join("products", "products.id", "=", "product_orders.product_id")
                ->join("user_markets", "user_markets.market_id", "=", "products.market_id")
                ->where(function ($query){
                    $query->where('type', Order::PRODUCT_TYPE)
                        ->where('user_markets.user_id', auth()->id())
                        ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_RAZORPAY)
                        ->where('payment_status', 'SUCCESS');
                })
                ->orWhere(function ($query){
                    $query->where('type', Order::PRODUCT_TYPE)
                        ->where('user_markets.user_id', auth()->id())
                        ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_COD);
                })
//                ->orderBy('order_status_id', Order::STATUS_RECEIVED)
                ->groupBy('orders.id')
                ->select('orders.*');

        } else if (auth()->user()->hasRole('client')) {

            return $model->newQuery()->with("user")->with("orderStatus")
                ->with('payment')->with("market.field")
                ->where(function ($query){
                    $query->where('type', Order::PRODUCT_TYPE)
                        ->where('orders.user_id', auth()->id())
                        ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_RAZORPAY)
                        ->where('payment_status', 'SUCCESS');
                })
                ->orWhere(function ($query){
                    $query->where('type', Order::PRODUCT_TYPE)
                        ->where('orders.user_id', auth()->id())
                        ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_COD);
                })
//                ->orderBy('order_status_id', Order::STATUS_RECEIVED)
                ->groupBy('orders.id')
                ->select('orders.*');
        } else if (auth()->user()->hasRole('driver')) {
            return $model->newQuery()->with("user")->with("orderStatus")->with('payment')
                ->where(function ($query){
                    $query->where('type', Order::PRODUCT_TYPE)
                        ->where('orders.driver_id', auth()->id())
                        ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_RAZORPAY)
                        ->where('payment_status', 'SUCCESS');
                })
                ->orWhere(function ($query){
                    $query->where('type', Order::PRODUCT_TYPE)
                        ->where('orders.driver_id', auth()->id())
                        ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_COD);
                })
//                ->orderBy('order_status_id', Order::STATUS_RECEIVED)
                ->groupBy('orders.id')
                ->select('orders.*');
        }
        else {
            return $model->newQuery()
                ->with("user"
                )->with("market.field")
                ->with("orderStatus")
                ->with('payment')
                ->where('type', Order::PRODUCT_TYPE);
//                ->orderBy('order_status_id', Order::STATUS_RECEIVED);
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
            ->parameters(array_merge(
                [
                    'language' => json_decode(
                        file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                        ), true),
                    'order' => [ [0, 'desc'] ],
                ],
                config('datatables-buttons.parameters')
            ));
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
        return 'ordersdatatable_' . time();
    }
}