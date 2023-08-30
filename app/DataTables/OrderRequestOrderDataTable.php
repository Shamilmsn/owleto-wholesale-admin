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
use App\Models\Market;
use App\Models\Order;
use App\Models\PaymentMethod;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class OrderRequestOrderDataTable extends DataTable
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
            ->editColumn('id', function ($order) {
                return "#".$order->id;
            })
            ->editColumn('updated_at', function ($order) {
                return getDateColumn($order, 'updated_at');
            })
            ->editColumn('delivery_fee', function ($order) {
                return getPriceColumn($order, 'delivery_fee');
            })
            ->editColumn('user.name', function ($order) {
                if(!$order->user){
                    return '-';
                }
                return $order->user->name;
            })
            ->editColumn('market.field.name', function ($order) {
                if(!$order->market){
                    return '-';
                }
                return $order->market->name;
            })
//            ->editColumn('tax', function ($order) {
//
//                return getPriceColumn($order, 'tax');
//            })
            ->editColumn('order.status', function ($order) {
                return $order->orderStatus->status;
            })
//            ->editColumn('payment.status', function ($order) {
//                return getPayment($order->payment,'status');
//            })
            ->editColumn('delivery_type.name', function ($order) {
                return optional($order->deliveryType)->name;
            })
            ->editColumn('payment.method', function ($order) {

                if ($order->payment_method_id == PaymentMethod::PAYMENT_METHOD_WALLET) {
                    return 'Wallet';
                }

                return optional($order->paymentMethod)->name;
            })
            ->editColumn('active', function ($product) {
                return getBooleanColumn($product, 'active');
            })
            ->addColumn('vendor_payment',function ($order){
                return $order->sub_total - $order->owleto_commission_amount;
            })
            ->editColumn('driver_commission_amount',function ($order){
                return $order->driver_commission_amount.'<p class="small">Distance : '.round($order->driver_total_distance,3).'</p>';
            })
            ->addColumn('action', function ($order){
                return view('order_request_orders.datatables_actions', compact('order'));
            })
            ->rawColumns(array_merge($columns, ['action']));

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
                'data' => 'id',
                'title' => trans('lang.order_id'),

            ],
            [
                'data' => 'user.name',
                'name' => 'user.name',
                'title' => trans('lang.order_user_id'),

            ],
            [
                'data' => 'market.name',
                'name' => 'market.name',
                'title' => trans('lang.market'),

            ],
            [
                'data' => 'order_status.status',
                'name' => 'orderStatus.status',
                'title' => trans('lang.order_order_status_id'),

            ],
            [
                'data' => 'driver_commission_amount',
                'name' => 'driver_commission_amount',
                'title' => 'Driver Commission',

            ],
            [
                'data' => 'owleto_commission_amount',
                'name' => 'owleto_commission_amount',
                'title' => 'Owleto Commission',

            ],
            [
                'data' => 'vendor_payment',
                'name' => 'vendor_payment',
                'title' => 'Vendor Commission',

            ],
//            [
//                'data' => 'tax',
//                'title' => trans('lang.order_tax')."<span>" . ' '.   setting('default_currency') . "</span>",
//                'searchable' => false,
//
//            ],
            [
                'data' => 'delivery_fee',
                'title' => trans('lang.order_delivery_fee')."<span>" . ' '.   setting('default_currency') . "</span>",
                'searchable' => false,

            ],
//            [
//                'data' => 'payment.status',
//                'name' => 'payment.status',
//                'title' => trans('lang.payment_status'),
//
//            ],
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
//            [
//                'data' => 'deliveryType.name',
//                'name' => 'deliveryType.name',
//                'title' => trans('lang.delivery_type'),
//
//            ],
//            [
//                'data' => 'payment_method_id',
//                'name' => 'payment_method_id',
//                'title' => trans('lang.payment_method'),
//
//            ],
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

            return $model->newQuery()
                ->with('deliveryType')
                ->with("user")
                ->with("orderStatus")
                ->with("market.field")
                ->with('payment')
                ->join('markets', 'markets.id', '=', 'orders.market_id')
                ->where(function ($query) use ($user){
                    $query->where('type', Order::ORDER_REQUEST_TYPE)
//                        ->where('markets.city_id', $user->city_id)
                        ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_RAZORPAY)
                        ->where('payment_status', 'SUCCESS');

                    $query->orWhere(function ($query) use ($user){
                        $query->where('type', Order::ORDER_REQUEST_TYPE)
                            ->whereIn("payment_method_id",[ PaymentMethod::PAYMENT_METHOD_COD, PaymentMethod::PAYMENT_METHOD_WALLET]);
                    });
                })
//                ->orWhere(function ($query) use ($user){
//                    $query->where('type', Order::ORDER_REQUEST_TYPE)
//                          ->whereIn("payment_method_id",[ PaymentMethod::PAYMENT_METHOD_COD, PaymentMethod::PAYMENT_METHOD_WALLET]);
//                })
                ->orderBy('orders.created_at', 'desc')
                ->select('orders.*');

        } else if (auth()->user()->hasRole('vendor_owner')) {

            $userMarkets = Market::whereHas('users', function ($query) {
                $query->where('id', Auth::id());
            })->pluck('id');

            return $model->newQuery()
                ->with("user")
                ->with("orderStatus")
                ->with('payment')
                ->with("market.field")
                ->whereIn('orders.market_id', $userMarkets)
                ->where(function ($query) use ($userMarkets) {
                    $query ->where('type', Order::ORDER_REQUEST_TYPE)
                        ->whereIn('market_id', $userMarkets)
                        ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_RAZORPAY)
                        ->where('payment_status', 'SUCCESS');

                    $query->orWhere(function ($query) use ($userMarkets) {
                        $query->where('type', Order::ORDER_REQUEST_TYPE)
                            ->whereIn('market_id', $userMarkets)
                            ->whereIn("payment_method_id",[ PaymentMethod::PAYMENT_METHOD_COD, PaymentMethod::PAYMENT_METHOD_WALLET]);
                    });
                })
//                ->orWhere(function ($query) use ($userMarkets) {
//                    $query->where('type', Order::ORDER_REQUEST_TYPE)
//                        ->whereIn('market_id', $userMarkets)
//                        ->whereIn("payment_method_id",[ PaymentMethod::PAYMENT_METHOD_COD, PaymentMethod::PAYMENT_METHOD_WALLET]);
//                })
                ->orderBy('orders.created_at', 'desc')
                ->select('orders.*');

        } else if (auth()->user()->hasRole('client')) {

            return $model->newQuery()->with("user")->with("orderStatus")
                ->with('payment')->with("market.field")
                ->where(function ($query){
                    $query->where('orders.user_id', auth()->id())
                        ->where('type', Order::ORDER_REQUEST_TYPE)
                        ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_RAZORPAY)
                        ->where('payment_status', 'SUCCESS');

                    $query->orWhere(function ($query){
                        $query->where('orders.user_id', auth()->id())
                            ->where('type', Order::ORDER_REQUEST_TYPE)
                            ->whereIn("payment_method_id",[ PaymentMethod::PAYMENT_METHOD_COD, PaymentMethod::PAYMENT_METHOD_WALLET]);
                    });
                })
//                ->orWhere(function ($query){
//                    $query->where('orders.user_id', auth()->id())
//                        ->where('type', Order::ORDER_REQUEST_TYPE)
//                        ->whereIn("payment_method_id",[ PaymentMethod::PAYMENT_METHOD_COD, PaymentMethod::PAYMENT_METHOD_WALLET]);
//                })
                ->orderBy('orders.created_at', 'desc')
                ->select('orders.*');

        } else if (auth()->user()->hasRole('driver')) {

            return $model->newQuery()->with("user")->with("market.field")
                ->with("orderStatus")->with('payment')
                ->where(function ($query){
                    $query->where('orders.driver_id', auth()->id())
                        ->where('type', Order::ORDER_REQUEST_TYPE)
                        ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_RAZORPAY)
                        ->where('payment_status', 'SUCCESS');

                    $query->orWhere(function ($query){
                        $query->where('orders.driver_id', auth()->id())
                            ->where('type', Order::ORDER_REQUEST_TYPE)
                            ->whereIn("payment_method_id",[ PaymentMethod::PAYMENT_METHOD_COD, PaymentMethod::PAYMENT_METHOD_WALLET]);
                    });
                })
//                ->orWhere(function ($query){
//                    $query->where('orders.driver_id', auth()->id())
//                        ->where('type', Order::ORDER_REQUEST_TYPE)
//                        ->whereIn("payment_method_id",[ PaymentMethod::PAYMENT_METHOD_COD, PaymentMethod::PAYMENT_METHOD_WALLET]);
//                })
                ->orderBy('orders.created_at', 'desc')
                ->select('orders.*');

        } else {

            return $model->newQuery()
                ->with("user")
                ->with("market.field")
                ->with("orderStatus")
                ->with('payment')
                ->where(function ($query){
                    $query->where('type', Order::ORDER_REQUEST_TYPE)
                        ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_RAZORPAY)
                        ->where('payment_status', 'SUCCESS');

                    $query->orWhere(function ($query){
                        $query->where('type', Order::ORDER_REQUEST_TYPE)
                            ->whereIn("payment_method_id",[ PaymentMethod::PAYMENT_METHOD_COD, PaymentMethod::PAYMENT_METHOD_WALLET]);
                    });
                })
//                ->orWhere(function ($query){
//                    $query->where('type', Order::ORDER_REQUEST_TYPE)
//                        ->whereIn("payment_method_id",[ PaymentMethod::PAYMENT_METHOD_COD, PaymentMethod::PAYMENT_METHOD_WALLET]);
//                })
                ->orderBy('orders.created_at', 'desc')
                ->select('orders.*');
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
