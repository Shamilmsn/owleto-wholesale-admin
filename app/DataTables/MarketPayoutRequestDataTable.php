<?php

namespace App\DataTables;

use App\Models\DriverPayoutRequest;
use App\Models\MarketPayoutRequest;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class MarketPayoutRequestDataTable extends DataTable
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
            ->editColumn('user.name', function ($payoutRequest) {
                return $payoutRequest->user->name;
            })
            ->editColumn('markets.name', function ($payoutRequest) {
                return $payoutRequest->market->name;
            })
            ->editColumn('amount', function ($payoutRequest) {
                    return $payoutRequest->amount ?? 0 ;
            })
            ->editColumn('paid_amount', function ($payoutRequest) {
                return $payoutRequest->paid_amount ?? 0 ;
            })
            ->editColumn('created_at', function ($driverPayoutRequest) {
                return getDateColumn($driverPayoutRequest, 'created_at');
            })
            ->editColumn('status', function ($driverPayoutRequest) {
                return $driverPayoutRequest->status;
            })
            ->addColumn('action', 'market-payout-requests.datatables_actions')
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
                'data' => 'markets.name',
                'title' => 'Market',
            ],
            [
                'data' => 'amount',
                'title' => 'Amount',

            ],
            [
                'data' => 'paid_amount',
                'title' => 'Paid Amount',

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
     * @param \App\Models\MarketPayoutRequest $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(MarketPayoutRequest $model)
    {
        if(auth()->user()->hasRole('admin')) {
            return $model->newQuery()->with("user")
                ->where('market_payout_requests.status', '!=', MarketPayoutRequest::PAID)
                ->select('market_payout_requests.*');
        }
        elseif(auth()->user()->hasRole('vendor_owner'))
        {
            return $model->newQuery()->with("user")
                ->where('user_id',Auth::id())
                ->where('market_payout_requests.status', '!=', MarketPayoutRequest::PAID)
                ->select('market_payout_requests.*');
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
        return 'market_payout_request_datatable_' . time();
    }
}