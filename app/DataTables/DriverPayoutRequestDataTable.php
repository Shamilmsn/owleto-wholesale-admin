<?php

namespace App\DataTables;

use App\Models\DriverPayoutRequest;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class DriverPayoutRequestDataTable extends DataTable
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
            ->editColumn('paid_amount', function ($driverPayoutRequest) {
                    return $driverPayoutRequest->paid_amount ?? 0 ;
            })
            ->editColumn('created_at', function ($driverPayoutRequest) {
                return getDateColumn($driverPayoutRequest, 'created_at');
            })
            ->addColumn('action', 'driver-payout-requests.datatables_actions')
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
                'title' => trans('lang.name'),

            ],
            [
                'data' => 'amount',
                'title' => trans('lang.amount'),

            ],
            [
            'data' => 'paid_amount',
            'title' => trans('lang.paid_amount'),

            ],
            [
                'data' => 'status',
                'title' => 'Status',
            ],
            [
                'data' => 'created_at',
                'title' => trans('lang.driver_payout_created_at'),
                'searchable' => false,
            ]
        ];

        return $columns;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\DriverPayoutRequest $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(DriverPayoutRequest $model)
    {
        return $model->newQuery()->with("user")
            ->where('driver_payout_requests.status', '!=', DriverPayoutRequest::PAID)
            ->select('driver_payout_requests.*');
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
        return 'driver_payout_request_datatable_' . time();
    }
}