<?php
/**
 * File name: PaymentDataTable.php
 * Last modified: 2020.05.04 at 09:04:19
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class PaymentDataTable extends DataTable
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
            ->editColumn('updated_at', function ($payment) {
                return getDateColumn($payment, 'updated_at');
            })
            ->editColumn('price', function ($payment) {
                return getPriceColumn($payment);
            })
            ->addColumn('action', 'payments.datatables_actions')
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Post $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Payment $model)
    {
        $user = auth()->user();
        $cityId = $user->city_id;

        if (auth()->user()->hasRole('admin')) {

            return $model->newQuery()->with("user")
                    ->with('order.market')
                    ->whereHas('order.market', function ($query) use($cityId) {
                        $query->where('city_id', $cityId);
                    })
                ->select('payments.*')
                ->orderBy('id', 'desc');
        } else if(auth()->user()->hasRole('vendor_owner')){
            return $model->newQuery()->with("user")
                ->join("orders", "payments.id", "=", "orders.payment_id")
                ->join("product_orders", "orders.id", "=", "product_orders.order_id")
                ->join("products", "products.id", "=", "product_orders.product_id")
                ->join("user_markets", "user_markets.market_id", "=", "products.market_id")
                ->where('user_markets.user_id', auth()->id())
                ->with('order.market')
                ->whereHas('order.market', function ($query) use($cityId) {
                    $query->where('city_id', $cityId);
                })
                ->groupBy('payments.id')
                ->orderBy('payments.id', 'desc')
                ->select('payments.*');
        } else if (auth()->user()->hasRole('client')) {
            return $model->newQuery()->with("user")
                ->with('order.market')
                ->whereHas('order.market', function ($query) use($cityId) {
                    $query->where('city_id', $cityId);
                })
                ->where('payments.user_id', auth()->id())
                ->select('payments.*')->orderBy('id', 'desc');
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
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $columns = [
            [
                'data' => 'price',
                'title' => trans('lang.payment_price')."<span>" . ' '.   setting('default_currency') . "</span>",

            ],
            [
                'data' => 'description',
                'title' => trans('lang.payment_description'),

            ],
            (auth()->check() && auth()->user()->hasAnyRole(['admin','vendor_owner'])) ? [
                'data' => 'user.name',
                'title' => trans('lang.payment_user_id'),

            ] : null,
            [
                'data' => 'method',
                'title' => trans('lang.payment_method'),

            ],
            [
                'data' => 'status',
                'title' => trans('lang.payment_status'),

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.payment_updated_at'),
                'searchable' => true,
            ]
        ];
        $columns = array_filter($columns);
        $hasCustomField = in_array(Payment::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Payment::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.payment_' . $field->name),
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
        return 'paymentsdatatable_' . time();
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
