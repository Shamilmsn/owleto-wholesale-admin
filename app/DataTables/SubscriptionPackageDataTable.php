<?php

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\DeliveryTime;
use App\Models\PackageOrder;
use App\Models\SubscriptionPackage;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Html\Editor\Editor;

class SubscriptionPackageDataTable extends DataTable
{

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

            ->editColumn('name', function ($package) {
                return $package->name;
            })
            ->editColumn('quantity', function ($package) {
                return $package->quantity;
            })
            ->editColumn('delivery_time', function ($package) {

                $packageDeliveryTimes = $package->package_delivery_times()->pluck('package_delivery_times.delivery_time_id');
                if($packageDeliveryTimes->count() > 0) {
                    $deliveryTimes = DeliveryTime::whereIn('id', $packageDeliveryTimes)->pluck('name')->toArray();
                    return implode(',', $deliveryTimes);
                }

            })
            ->editColumn('days', function ($package) {
                return $package->days;
            })
            ->addColumn('action', function ($package){
                $isActiveSubscriptions = PackageOrder::where('delivered', false)
                    ->pluck('package_id')->toArray();

                return view('packages.datatables_actions', compact('package','isActiveSubscriptions'));
            })
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\SubscriptionPackage $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(SubscriptionPackage $model)
    {
         $package =  $model->newQuery()->orderBy('id','DESC');
         return $package;
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
                'data' => 'name',
                'title' => trans('lang.package_name'),

            ],
//            [
//                'data' => 'product_id',
//                'title' => trans('lang.package_product_id'),
//
//            ],
//            [
//                'data' => 'user_id',
//                'title' => trans('lang.package_market_id'),
//
//            ],
            [
                'data' => 'quantity',
                'title' => trans('lang.package_quantity'),
                'searchable' => false,
            ],
            [
                'data' => 'delivery_time',
                'title' => trans('lang.package_delivery_time'),
                'searchable' => false,
            ],
            [
                'data' => 'days',
                'title' => trans('lang.package_days'),
                'searchable' => false,
            ],
        ];

        $hasCustomField = in_array(SubscriptionPackage::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', SubscriptionPackage::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.product_' . $field->name),
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
        return 'SubscriptionPackage_' . date('YmdHis');
    }
}
