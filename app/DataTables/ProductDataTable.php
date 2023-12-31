<?php
/**
 * File name: ProductDataTable.php
 * Last modified: 2020.05.04 at 09:04:18
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class ProductDataTable extends DataTable
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
            ->editColumn('image', function ($product) {
                return getMediaColumn($product, 'image');
            })
            ->editColumn('base_name', function ($product) {
                if ($product->is_base_product == Product::BASE_PRODUCT ) {
                    return $product->base_name;
                }
                return $product->base_name . " - " . $product->variant_name;
            })
            ->editColumn('price', function ($product) {
                return getPriceColumn($product);
            })
            ->editColumn('discount_price', function ($product) {
                return getPriceColumn($product,'discount_price');
            })
            ->editColumn('capacity', function ($product) {
                return $product['capacity']." ".$product['unit'];
            })
            ->editColumn('updated_at', function ($product) {
                return getDateColumn($product, 'updated_at');
            })
//            ->editColumn('featured', function ($product) {
//                return getBooleanColumn($product, 'featured');
//            })
            ->editColumn('is_base_product', function ($product) {
                return getBooleanColumn($product, 'is_base_product');
            })
            ->addColumn('action', function ($product) {
                return view('products.datatables_actions', compact('product'));
            })
            ->setRowClass(function ($product) {
                return $product->product_type == 3 ? 'bg-info' : 'bg-white';
            })
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Product $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Product $model)
    {

        if (auth()->user()->hasRole('admin')) {

            $user = auth()->user();
            return $model->newQuery()
                ->with("market")
                ->with("category")
                ->whereHas('market')
                ->rightjoin("markets", "markets.id", "=", "products.market_id")
//                ->where("markets.city_id", $user->city_id)
            ->select('products.*')
            ->orderBy('products.updated_at','desc');

        } else if (auth()->user()->hasRole('vendor_owner')) {
            return $model->newQuery()->with("market")->with("category")
                ->join("user_markets", "user_markets.market_id", "=", "products.market_id")
                ->where('user_markets.user_id', auth()->id())
                ->groupBy('products.id')
                ->select('products.*')->orderBy('products.updated_at', 'desc');

        } else if (auth()->user()->hasRole('driver')) {
            return $model->newQuery()->with("market")->with("category")
                ->join("driver_markets", "driver_markets.market_id", "=", "products.market_id")
                ->where('driver_markets.user_id', auth()->id())
                ->groupBy('products.id')
                ->select('products.*')->orderBy('products.updated_at', 'desc');
        } else if (auth()->user()->hasRole('client')) {
            return $model->newQuery()->with("market")->with("category")
                ->join("product_orders", "product_orders.product_id", "=", "products.id")
                ->join("orders", "product_orders.order_id", "=", "orders.id")
                ->where('orders.user_id', auth()->id())
                ->groupBy('products.id')
                ->select('products.*')->orderBy('products.updated_at', 'desc');
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
                'data' => 'id',
                'title' => trans('lang.id'),
                'searchable' => true,

            ],
            [
                'data' => 'base_name',
                'title' => trans('lang.product_name'),
                'searchable' => true,

            ],
            [
                'data' => 'image',
                'title' => trans('lang.product_image'),
                'searchable' => false, 'orderable' => false, 'exportable' => false, 'printable' => false,
            ],
            [
                'data' => 'is_base_product',
                'title' => trans('lang.is_base_product'),

            ],
            [
                'data' => 'price',
                'title' => trans('lang.product_price'). "<span>" . ' '.   setting('default_currency') . "</span>",

            ],
            [
                'data' => 'discount_price',
                'title' => trans('lang.product_discount_price'). "<span>" . ' '.   setting('default_currency') . "</span>",

            ],
            [
                'data' => 'tax',
                'title' => 'Tax Amount',
                'visible' => Auth::user()->hasRole('admin')
            ],
            [
                'data' => 'price_without_gst',
                'title' => 'Price Exclude Tax',
                'visible' => Auth::user()->hasRole('admin')
            ],
            [
                'data' => 'tcs_amount',
                'title' => 'TCS Amount',
                'visible' => Auth::user()->hasRole('admin')
            ],
            [
                'data' => 'tds_amount',
                'title' => 'TDS Amount',
                'visible' => Auth::user()->hasRole('admin')
            ],
            [
                'data' => 'owleto_commission_amount',
                'title' => 'Owleto Commission Amount',
                'visible' => Auth::user()->hasRole('admin')
            ],
            [
                'data' => 'eighty_percentage_of_commission_amount',
                'title' => '18% of Commission Amount',
                'visible' => Auth::user()->hasRole('admin')
            ],
            [
                'data' => 'vendor_payment_amount',
                'title' => 'Vendor Payment',
                'visible' => Auth::user()->hasRole('admin')
            ],
            [
                'data' => 'market.name',
                'title' => trans('lang.product_market_id'),

            ],
            [
                'data' => 'category.name',
                'title' => trans('lang.product_category_id'),

            ],
        ];

        $hasCustomField = in_array(Product::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Product::class)->where('in_table', '=', true)->get();
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
        return 'productsdatatable_' . time();
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
