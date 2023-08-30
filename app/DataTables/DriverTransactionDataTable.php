<?php

namespace App\DataTables;

use App\Models\DriverTransaction;
use App\Models\MarketTransaction;
use App\Models\OrderStatus;
use App\Models\CustomField;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class DriverTransactionDataTable extends DataTable
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
            ->editColumn('user.name', function ($market_transaction) {
                if($market_transaction->user){
                    return $market_transaction->user->name;
                }

                return '-';
            })
            ->editColumn('created_at',function($market_transaction){
                return getDateColumn($market_transaction,'created_at');
            })
//            ->addColumn('action', 'order_statuses.datatables_actions')
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\MarketTransaction $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(DriverTransaction $model)
    {
        $user = User::find(Auth::id());

        return $model->newQuery()->with("user")
            ->join('users', 'users.id', '=', 'driver_transactions.user_id')
            ->where('users.city_id', $user->city_id)
            ->orderBy('id','desc')
            ->select('driver_transactions.*');
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
                'data' => 'user.name',
                'name' => 'user.name',
                'title' => trans('lang.order_user_id'),

            ],
                [
                    'data' => 'credit',
                    'title' => trans('lang.credit_place_holder'),
                    'searchable' => true,
                ],
                [
                    'data' => 'debit',
                    'title' => trans('lang.debit_place_holder'),
                    'searchable' => true,
                ],
                [
                    'data' => 'balance',
                    'title' => trans('lang.balance_place_holder'),
                    'searchable' => true,
                ],
                [
                  'data' => 'created_at',
                  'title' => trans('lang.created_at_placeholder'),
                    'searchable' => true,
                    'orderable' => true,
                ]
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
        return 'market_transactions' . time();
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