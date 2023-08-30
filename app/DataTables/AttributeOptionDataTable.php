<?php

namespace App\DataTables;

use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\CustomField;
use App\Models\ProductAttributeOption;
use App\Models\ProductOrder;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Barryvdh\DomPDF\Facade as PDF;

class AttributeOptionDataTable extends DataTable
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
            ->editColumn('updated_at',function($attributeOption){
            return getDateColumn($attributeOption,'updated_at');
            })
            ->addColumn('action', function ($attributeOption){
                $activeAttributesOption =  ProductAttributeOption::where('attribute_option_id', $attributeOption->id)->get();
                return view('attribute_options.datatables_actions', compact('attributeOption','activeAttributesOption'));
            })
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AttributeOption $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AttributeOption $model)
    {
        return $model->newQuery()->with("attribute")->orderBy('created_at', 'desc');
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
            [
              'data' => 'name',
              'title' => trans('lang.attribute_name'),
            ],
            [
                'data' => 'attribute.name',
                'title' => trans('lang.attribute'),

            ],
            [
                'data' => 'meta',
                'title' => trans('lang.meta'),
            ],
            [
              'data' => 'updated_at',
              'title' => trans('lang.attribute_updated_at'),
              'searchable'=>false,
]
            ];

        $hasCustomField = in_array(AttributeOption::class, setting('custom_field_models',[]));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', AttributeOption::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.attribute_option' . $field->name),
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
        return 'attribute_options_datatable_' . time();
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