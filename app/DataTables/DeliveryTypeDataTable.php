<?php
/**
 * File name: FieldDataTable.php
 * Last modified: 2020.05.04 at 09:04:18
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\DeliveryType;
use App\Models\Field;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class DeliveryTypeDataTable extends DataTable
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
            ->editColumn('updated_at', function ($deliveryType) {
                return getDateColumn($deliveryType, 'updated_at');
            })
            ->addColumn('action', 'delivery_types.datatables_actions')
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
                'data' => 'name',
                'title' => trans('lang.delivery_type_name'),

            ],
//            [
//                'data' => 'start_time',
//                'title' => 'Start Time',
//
//            ], [
//                'data' => 'end_time',
//                'title' =>'End Time',
//
//            ],
            [
                'data' => 'charge',
                'title' => trans('lang.charge_name'),

            ],
            [
                'data' => 'base_distance',
                'title' => 'Distance',

            ],
            [
                'data' => 'additional_amount',
                'title' => 'Additional Amount',

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.field_updated_at'),
                'searchable' => false,
            ]
        ];
        $columns = array_filter($columns);

        $hasCustomField = in_array(DeliveryType::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', DeliveryType::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $deliveryType) {
                array_splice($columns, $deliveryType->order - 1, 0, [[
                    'data' => 'custom_fields.' . $deliveryType->name . '.view',
                    'title' => trans('lang.delivery_type' . $deliveryType->name),
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
     * @param \App\Models\DeliveryType $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(DeliveryType $model)
    {
        return $model->newQuery();
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
        return 'deliveryTypesDatatable_' . time();
    }
}
