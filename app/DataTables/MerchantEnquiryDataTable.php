<?php

namespace App\DataTables;

use App\Models\Area;
use App\Models\City;
use App\Models\MerchantRequest;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Barryvdh\DomPDF\Facade as PDF;

class MerchantEnquiryDataTable extends DataTable
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
            ->editColumn('action', function ($merchantRequest) {
                return view('merchant-requests.datatables_actions', compact('merchantRequest'));
            })
            ->rawColumns(array_merge($columns, ['action']));
        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\MerchantRequest $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(MerchantRequest $model)
    {
        return $model->newQuery()
            ->orderBy('id','desc');
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
                'data' => 'name',
                'title' => 'Name',
                'searchable' => true,
            ],
            [
                'data' => 'email',
                'title' => 'Email',
                'searchable' => true,
            ],
            [
                'data' => 'phone',
                'title' => 'Phone',
                'searchable' => true,
            ],
            [
                'data' => 'description',
                'title' => 'Description',
                'searchable' => true,
            ],
            [
                'data' => 'status',
                'title' => 'Status',
                'searchable' => true,
            ],
            [
                'data' => 'action',
                'title' => 'Action',
                'searchable' => false,
            ],
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
        return 'merchant_requests_datatable_' . time();
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