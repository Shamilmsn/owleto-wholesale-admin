<?php

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\PackageOrder;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class PackageDetailDataTable extends DataTable
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
            ->editColumn('package_price',function ($packageOrder){
                return $packageOrder->price_per_delivery;
            })

            ->editColumn('order_status_id', function ($packageOrder) {
                if($packageOrder->orderStatus) {
                    return  $packageOrder->orderStatus->status;
                }else{
                    return ' ';
                }
            })
            ->addColumn('action', function ($packageOrder) {
                return view('package_orders.details_datatables_actions', compact('packageOrder'));
            })
            ->addColumn('Vendor_Commission',function ($packageOrder){
                return $packageOrder->price_per_delivery - $packageOrder->commission_amount;
            })
            ->editColumn('driver_commission_amount',function ($order){
                return $order->driver_commission_amount.'<p class="small">Distance : '.round($order->driver_total_distance,3).'</p>';
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

        return $model->newQuery()
            ->with("package")
            ->with("orderStatus")
            ->where('package_orders.order_id', $this->id)
            ->select('package_orders.*')
            ->orderBy('package_orders.date','ASC');

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
                'data' => 'driver_commission_amount',
                'title' => 'Driver Commission',
            ],
            [
                'data' => 'owleto_commission_amount',
                'title' => 'Owleto Commission',
            ],
            [
                'data' => 'Vendor_Commission',
                'title' => 'Vendor Commission'
            ],
            [
                'data' => 'date',
                'title' => trans('lang.package_order_date'),
                'orderable' => true,

            ],
            [
//                'data' => 'order_status_id',
//                'title' => trans('lang.status'),
//                'orderable' => false,
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