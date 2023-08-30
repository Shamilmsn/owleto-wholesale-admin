<?php
/**
 * File name: OrderDataTable.php
 * Last modified: 2020.04.30 at 08:21:08
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\DataTables;

use App\Models\Market;
use App\Models\PackageOrder;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class TodayPackageOrderDatatable extends DataTable
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
            ->editColumn('updated_at', function ($packageOrder) {
                return getDateColumn($packageOrder, 'updated_at');
            })
            ->editColumn('package.name', function ($packageOrder) {
                return $packageOrder->package->name;
            })
            ->editColumn('package_price', function ($packageOrder) {
                return $packageOrder->package_price;
            })
            ->editColumn('date', function ($packageOrder) {
                return Carbon::parse($packageOrder->date)->format('d M Y');
            })
            ->editColumn('delivered', function ($packageOrder) {
                return getBooleanColumn($packageOrder, 'delivered');
            })
            ->editColumn('canceled', function ($packageOrder) {
                return getBooleanColumn($packageOrder, 'canceled');
            })
            ->editColumn('order.status', function ($order) {
                return $order->orderStatus->status;
            })

            ->editColumn('order_status_id', function ($packageOrder) {
                if($packageOrder->orderStatus) {
                    return  $packageOrder->orderStatus->status;
                }else{
                    return ' ';
                }
            })
            ->addColumn('action', function ($packageOrder) {
                return view('package_orders.current-date.datatables_actions', compact('packageOrder'));
            })
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\PackageOrder $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(PackageOrder $model)
    {
        if (auth()->user()->hasRole('admin')) {

            return $model->newQuery()
                ->with("package")
                ->with("orderStatus")
                ->whereDate('package_orders.date', Carbon::today())
                ->orderBy('package_orders.created_at', 'desc')
                ->select('package_orders.*');

        }
        if (auth()->user()->hasRole('vendor_owner')) {

            $userMarkets = Market::whereHas('users', function ($query){
                $query->where('id', Auth::id());
            })->pluck('id');

            return $model->newQuery()
                ->with("package")
                ->with("orderStatus")
                ->whereDate('package_orders.date', Carbon::today())
                ->whereIn('package_orders.market_id', $userMarkets)
                ->orderBy('package_orders.created_at', 'desc')
                ->select('package_orders.*');

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
                'data' => 'package.name',
                'name' => 'package.name',
                'title' => trans('lang.package_order_package_id'),
                'searchable' => true,

            ],
            [
                'data' => 'package_price',
                'title' => trans('lang.package_order_price'),
                'orderable' => false,

            ],
            [
                'data' => 'date',
                'title' => trans('lang.package_order_date'),
                'orderable' => true,

            ],
            [
                'data' => 'order_status.status',
                'name' => 'orderStatus.status',
                'title' => trans('lang.order_order_status_id'),
            ],
            [
                'data' => 'delivered',
                'title' => trans('lang.package_order_is_delivered'),
                'orderable' => false,

            ],
            [
                'data' => 'canceled',
                'title' => trans('lang.package_order_is_canceled'),
                'orderable' => false,

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
