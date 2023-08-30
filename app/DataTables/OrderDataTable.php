<?php

namespace App\DataTables;

use App\Models\Field;
use App\Models\Market;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Services\DataTable;

class OrderDataTable extends DataTable
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
            ->filter(function ($query) {
                    if (request()->filled('filter.order_status_id')) {
                        $query->where(function ($query) {
                            $query->where('order_status_id', request('filter.order_status_id'));
                        });
                    }

                if (request()->filled('filter.method')) {
                    $query->where(function ($query) {
                        $query->where('payment_method_id', request('filter.method'));
                    });
                }

                if (request()->filled('filter.driver_id')) {
                    $query->where(function ($query) {
                        $query->where('driver_id', request('filter.driver_id'));
                    });
                }

                if (request()->filled('filter.delivery_type')) {
                    $query->where(function ($query) {
                        $query->where('delivery_type_id', request('filter.delivery_type'));
                    });
                }

                if (request()->filled('filter.market_id')) {
                    $query->where(function ($query) {
                        $query->where('market_id', request('filter.market_id'));
                    });
                }

                if (request()->filled('filter.search')) {
                    $query->where(function ($query) {
                        $query->where('id', request('filter.search'));
                    });
                }

                if (request()->filled('filter.start_date')) {
                    $startDate = date(request('filter.start_date'));
                    $endate = date(request('filter.end_date'));

                    $query->where(function ($query) use ( $startDate, $endate ) {
                        $query->whereDate('created_at', '>=', $startDate)
                              ->whereDate('created_at', '<=', $endate);
//                        $query->whereBetween('created_at',[$startDate, $endate]);
                    });
                }
            })->editColumn('id', function ($order) {
                return "#".$order->id;
            })
            ->editColumn('updated_at', function ($order) {
                return getDateColumn($order, 'updated_at');
            })
            ->editColumn('order_status_id', function ($order) {
                return optional($order->orderStatus)->status;
            })
            ->editColumn('delivery_type.name', function ($order) {
                if($order->deliveryType != NULL) {
                    return optional($order->deliveryType)->name;
                }else{
                    return 'Takeaway';
                }

            })
            ->editColumn('is_collected_from_driver', function ($order) {

                if ($order->is_collected_from_driver == 1) {
                    return 'Yes';
                }

                return 'No';

            })
            ->editColumn('payment.method', function ($order) {

                if ($order->payment_method_id == PaymentMethod::PAYMENT_METHOD_WALLET) {
                    return 'Wallet';
                }

                return optional($order->paymentMethod)->name;

            })
            ->editColumn('market_id', function ($order) {
                return optional($order->market)->name;
            })
            ->editColumn('driver_id', function ($order) {
                return optional($order->driver)->name;
            })
            ->addColumn('checkbox', function ($order) {
                if ($order->order_category != \App\Models\Order::VENDOR_BASED) {
                    return '';
                }
                if($order->order_status_id == OrderStatus::STATUS_DELIVERED ) {
                    return '';
                }
                return '<input class="ids-all-select" type="checkbox" name="ids[]" 
                data-id="' . $order->id . '">';
            })
//            ->editColumn('active', function ($product) {
//                return getBooleanColumn($product, 'active');
//            })
            ->addColumn('vendor_payment',function ($order){
                return $order->sub_total - $order->owleto_commission_amount;
            })
            ->addColumn('action', function ($order) {
                return view('orders.datatables_actions', compact('order'));
            })
            ->editColumn('driver_commission_amount',function ($order){
                return $order->driver_commission_amount.'<p class="small">Distance : '.round($order->driver_total_distance,3).'</p>';
            })
            ->rawColumns(array_merge($columns,['active','updated_at', 'action', 'checkbox']));

        return $dataTable;
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

            $query = $model->newQuery()
                ->whereNull('parent_id')
                ->with('deliveryType')
                ->with("user")
                ->with("orderStatus")
                ->with("market")
                ->with('payment')
                ->where('sector_id', '!=', Field::HOME_COOKED_FOOD)
//                ->whereHas('deliveryType', function ($query) {
//                    $query->where('isTimeType', 1);
//                })
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('type', Order::PRODUCT_TYPE)
                            ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_RAZORPAY)
                            ->where('payment_status', 'SUCCESS');
                    })->orWhere(function ($q) {
                        $q->where('type', Order::PRODUCT_TYPE)
                            ->whereIn("payment_method_id",[ PaymentMethod::PAYMENT_METHOD_COD, PaymentMethod::PAYMENT_METHOD_WALLET]);
                    });
                })->orderBy('orders.created_at', 'desc')
                ->select('orders.*');

        } else if (auth()->user()->hasRole('vendor_owner')) {

            $userMarketIds = Market::whereHas('users', function ($query){
                $query->where('user_id', Auth::id());
            })->pluck('id');

            $query = $model->newQuery()->with("user")
                ->where('order_category', Order::VENDOR_BASED)
                ->with("orderStatus")
                ->with('payment')
                ->with("market.field")
                ->join("product_orders", "orders.id", "=", "product_orders.order_id")
                ->join("products", "products.id", "=", "product_orders.product_id")
                ->join("user_markets", "user_markets.market_id", "=", "products.market_id")
                ->whereIn('orders.market_id', $userMarketIds)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('type', Order::PRODUCT_TYPE)
                            ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_RAZORPAY)
                            ->where('payment_status', 'SUCCESS');
                    })->orWhere(function ($q) {
                        $q->where('type', Order::PRODUCT_TYPE)
                          ->whereIn("payment_method_id",[ PaymentMethod::PAYMENT_METHOD_COD, PaymentMethod::PAYMENT_METHOD_WALLET]);
                    });
                })
                ->orderBy('orders.created_at', 'desc')
                ->select('orders.*');

        } else if (auth()->user()->hasRole('client')) {

            $query = $model->newQuery()->with("user")->with("orderStatus")
                ->with('payment')->with("market.field")
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('type', Order::PRODUCT_TYPE)
                            ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_RAZORPAY)
                            ->where('payment_status', 'SUCCESS');
                    })->orWhere(function ($q) {
                        $q->where('type', Order::PRODUCT_TYPE)
                            ->whereIn("payment_method_id",[ PaymentMethod::PAYMENT_METHOD_COD, PaymentMethod::PAYMENT_METHOD_WALLET]);
                    });
                })
                ->orderBy('orders.created_at', 'desc')
                ->select('orders.*');
        } else if (auth()->user()->hasRole('driver')) {
            $query = $model->newQuery()->with("user")->with("orderStatus")->with('payment')
                ->where('order_category', Order::VENDOR_BASED)
                ->where(function ($query) {
                    $query->where(function ($q) {
                            $q->where('type', Order::PRODUCT_TYPE)
                            ->where("payment_method_id", PaymentMethod::PAYMENT_METHOD_RAZORPAY)
                            ->where('payment_status', 'SUCCESS');
                    })->orWhere(function ($q) {
                            $q->where('type', Order::PRODUCT_TYPE)
                              ->whereIn("payment_method_id",[ PaymentMethod::PAYMENT_METHOD_COD, PaymentMethod::PAYMENT_METHOD_WALLET]);
                    });
                })
                ->orderBy('orders.created_at', 'desc')
                ->select('orders.*');
        }
        else {
            $query = $model->newQuery()
                ->with("user"
                )->with("market.field")
                ->with("orderStatus")
                ->with('payment')
                ->where('type', Order::PRODUCT_TYPE)
                ->orderBy('orders.created_at', 'desc')
                ->select('orders.*');
        }

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
            ->setTableId('tbl-order')
            ->columns($this->getColumns())
            ->minifiedAjax()
//            ->dom("<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-right'B>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>")
//            ->orderBy(4, 'desc')
            ->parameters([
                'searching' => false,
                'ordering' => false,
            ])
            ->buttons(
                Button::make('excel')
            );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            [
                'data' => 'id',
                'name' => 'id',
                'title' => trans('lang.order_id'),

            ],
            [
                'data' => 'user.name',
                'name' => 'user.name',
                'title' => 'Customer',

            ],
            [
                'data' => 'order_status_id',
                'name' => 'order_status_id',
                'title' => trans('lang.order_order_status_id'),

            ],
            [
                'data' => 'market_id',
                'name' => 'market_id',
                'title' => 'Market',
            ],
            [
                'data' => 'driver_id',
                'name' => 'driver_id',
                'title' => 'Driver',
                'visible' => request()->user()->hasRole('admin') ? true : false,
            ],
            [
                'data' => 'is_collected_from_driver',
                'title' => 'Is Collected?',

            ],
            [
                'data' => 'driver_commission_amount',
                'title' => 'Driver Commission',
                'visible' => request()->user()->hasRole('admin') ? true : false,
            ],
            [
                'data' => 'owleto_commission_amount',
                'title' => 'Owleto Commission',
                'visible' => request()->user()->hasRole('admin') ? true : false,
            ],
            [
                'data' => 'delivery_fee',
                'title' => 'Delivery Fee',
                'visible' => request()->user()->hasRole('admin') ? true : false,
            ],
            [
                'data' => 'vendor_payment',
                'title' => 'Vendor Commission',
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
//            [
//                'data' => 'active',
//                'title' => trans('lang.order_active'),
//
//            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.order_updated_at'),
                'searchable' => true,
                'orderable' => true,

            ],
            [
                'data' => 'action',
                'title' => 'Action',
                'searchable' => true,
                'orderable' => true,
            ],
            [
                'data' => 'checkbox',
                'name' => 'checkbox',
                'title' => '<div class="d-flex">
                        <input type="checkbox" id="select-all">
                        <button class="btn btn-primary btn-sm ml-2"
                         id="driver-assign">Assign Driver</button>
                    </div>'
            ]
            /* Column::make('id')->name('id')->title('#Booking'),
             Column::make('add your columns'),
             Column::make('created_at'),
             Column::make('updated_at'),
             Column::computed('action')
                 ->exportable(false)
                 ->printable(false)
                 ->width(60)
                 ->addClass('text-right'),*/
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'order' . date('YmdHis');
    }
}
