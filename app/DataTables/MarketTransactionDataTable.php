<?php

namespace App\DataTables;

use App\Models\Market;
use App\Models\MarketTransaction;
use App\Models\OrderStatus;
use App\Models\CustomField;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class MarketTransactionDataTable extends DataTable
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
            ->editColumn('created_at',function($market_transaction){
                return getDateColumn($market_transaction,'created_at');
            })
            ->editColumn('credit',function($query){
                return round($query->credit,2);
            })
            ->editColumn('balance',function($query){
                return round($query->balance,2);
            })
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\MarketTransaction $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(MarketTransaction $model)
    {
        $user = auth()->user();

        $userMarketIds = Market::whereHas('users', function ($query){
            $query->where('user_id', Auth::id());
        })->pluck('id');

        if (auth()->user()->hasRole('admin')) {

            return $model->newQuery()
                ->with('market')
                ->join('markets', 'markets.id', '=', 'market_transactions.market_id')
                ->where('markets.city_id', $user->city_id)
                ->orderBy('id','desc')
                ->select('market_transactions.*');

        } else if (auth()->user()->hasRole('vendor_owner')) {

            return $model->newQuery()
                ->join('markets', 'markets.id', '=', 'market_transactions.market_id')
                ->whereIn('markets.id', $userMarketIds)
                ->orderBy('id','desc')
                ->select('market_transactions.*');
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
//            ->addAction(['title'=>trans('lang.actions'),'width' => '80px', 'printable' => false ,'responsivePriority'=>'100'])
//            ->dom('<".row"<".col-lg-8 col-xs-8"l><".col-lg-3 col-xs-3"f>>rtip')
            ->parameters([
                'searching' => true,
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
                  'data' => 'market.name',
                  'title' => trans('lang.product_market_id'),
                ],
                [
                    'data' => 'credit',
                    'title' => 'Order Amount',
                ],
                [
                    'data' => 'debit',
                    'title' => 'Paid',
                ],
                [
                    'data' => 'balance',
                    'title' => trans('lang.balance_place_holder'),
                ],
                [
                  'data' => 'created_at',
                  'title' => trans('lang.created_at_placeholder'),
                  'searchable'=>false,
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