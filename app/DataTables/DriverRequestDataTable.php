<?php

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\Driver;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class DriverRequestDataTable extends DataTable
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
            ->editColumn('user.name', function ($driver) {
                return getLinksColumnByRouteName([$driver->user], "users.edit", 'id', 'name');
            })
            ->editColumn('user.phone', function ($driver) {
                return optional($driver->user)->phone;
            })
            ->editColumn('updated_at', function ($driver) {
                return getDateColumn($driver, 'updated_at');
            })
            ->editColumn('earning', function ($driver) {
                return getPriceColumn($driver, 'earning');
            })
            ->editColumn('balance', function ($driver) {
                return getPriceColumn($driver, 'balance');
            })
            ->editColumn('available', function ($driver) {
                return getBooleanColumn($driver, 'available');
            })
            ->addColumn('action', 'driver-requests.datatables_actions')
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
                'data' => 'user.name',
                'title' => 'Name',

            ],
            [
                'data' => 'user.phone',
                'title' => 'Phone',

            ],
            [
                'data' => 'delivery_fee',
                'title' => trans('lang.driver_delivery_fee'),

            ],

            [
                'data' => 'total_orders',
                'title' => trans('lang.driver_total_orders'),

            ],
            [
                'data' => 'earning',
                'title' => trans('lang.driver_earning')."<span>" . ' '.   setting('default_currency') . "</span>",

            ],
            [
                'data' => 'balance',
                'title' => trans('lang.driver_balance')."<span>" . ' '.   setting('default_currency') . "</span>",

            ],
            [
                'data' => 'available',
                'title' => trans('lang.driver_available'),

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.driver_updated_at'),
                'searchable' => false,
            ]
        ];

        $hasCustomField = in_array(Driver::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Driver::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.driver_' . $field->name),
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
     * @param \App\Models\Driver $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Driver $model)
    {
        if(auth()->user()->hasRole('admin')){
            $user = auth()->user();

            return $model->newQuery()
                ->with("user")
                ->join('users', 'users.id','=','drivers.user_id', )
                ->where('users.driver_signup_status', 4)
                ->where('drivers.admin_approved', false)
                ->select('drivers.*');

        }else if (auth()->user()->hasRole('vendor_owner')){

            $marketsIds = array_column(auth()->user()->markets->toArray(), 'id');

            return $model->newQuery()
                ->with("user")
                ->join('users', 'users.id','=','drivers.user_id', )
                ->where('users.driver_signup_status', 4)
                ->where('drivers.admin_approved', false)
                ->join('driver_markets','driver_markets.user_id','=','drivers.user_id')
                ->whereIn('driver_markets.market_id',$marketsIds)
                ->distinct('driver_markets.user_id')
                ->select('drivers.*');
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
        return 'driversdatatable_' . time();
    }
}