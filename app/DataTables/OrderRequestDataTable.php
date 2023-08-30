<?php

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\Market;
use App\Models\OrderRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class OrderRequestDataTable extends DataTable
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
            ->editColumn('user.name',function($orderRequest){
                if(!$orderRequest->user){
                    return '-';
                }
                if(!$orderRequest->user->name){
                    return '-';
                }
                return $orderRequest->user->name;
            })
            ->editColumn('user.phone',function($orderRequest){
                if(!$orderRequest->user){
                    return '-';
                }
                if(!$orderRequest->user->phone){
                    return '-';
                }
                return $orderRequest->user->phone;
            })
            ->editColumn('updated_at', function ($orderRequest) {
                return getDateColumn($orderRequest, 'updated_at');
            })
            ->editColumn('market.name',function($orderRequest){
                return $orderRequest->market->name;
            })
            ->editColumn('sector.name',function($orderRequest){

                return $orderRequest->sector->name;
            })
            ->editColumn('type',function($orderRequest){
                return OrderRequest::$types[$orderRequest->type];
            })

            ->addColumn('action', 'order_requests.datatables_actions')
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\OrderRequest $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(OrderRequest $model)
    {

        $user_id = auth()->id();

        if (auth()->user()->hasRole('admin') ) {

            $user = auth()->user();

            return $model->newQuery()->with('market')
                ->with('user')
                ->with('sector')
                ->join('markets', 'markets.id', '=', 'order_requests.market_id')
                ->where('markets.city_id', $user->city_id)
                ->orderBy('id', 'desc')
                ->select('order_requests.*');

        }
        else if (auth()->user()->hasRole('vendor_owner')) {

            $userMarkets = Market::whereHas('users', function ($query) use ($user_id){
                $query->where('id', $user_id);
            })->pluck('id');

            return $model->newQuery()
                ->with('user')
                ->with('sector')
                ->with('market')
                ->whereIn('market_id', $userMarkets)
                ->orderBy('id', 'desc')
                ->select('order_requests.*');
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
            ->addAction(['title'=>trans('lang.actions'),'width' => '80px', 'printable' => false ,'responsivePriority'=>'100'])
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
//            [
//                'data' => 'order_id',
//                'name' => 'order_id',
//                'title' => trans('lang.order_id'),
//
//            ],
            [
              'data' => 'user.name',
              'name' => 'user.name',
              'title' => trans('lang.order_request_user'),

            ],
            [
                'data' => 'user.phone',
                'name' => 'user.phone',
                'title' => trans('lang.market_phone'),

            ],
            [
              'data' => 'market.name',
              'name' => 'market.name',
              'title' => trans('lang.order_request_market'),
            ],
            [
                'data' => 'sector.name',
                'name' => 'sector.name',
                'title' => trans('lang.order_request_sector'),
            ],
            [
                'data' => 'type',
                'title' => trans('lang.order_request_type'),

            ],
//            [
//                'data' => 'order_text',
//                'title' => trans('lang.order_request_text'),
//            ],
            [
                'data' => 'distance',
                'name' => 'distance',
                'title' => trans('lang.distance'),
            ],
            [
                'data' => 'status',
                'title' => trans('lang.order_request_status'),
               // 'searchable' => true,
            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.order_request_updated_at'),
            ],
//            [
//                'data' => 'reviewed_by',
//                'title' => trans('lang.order_request_reviewed_by'),
//            ]
        ];

        $hasCustomField = in_array(OrderRequest::class, setting('custom_field_models',[]));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', OrderRequest::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.order_status_' . $field->name),
                    'orderable' => false,
                    'searchable' => false,
                ]]);
            }
        }
        return $columns;
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'order_statusesdatatable_' . time();
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