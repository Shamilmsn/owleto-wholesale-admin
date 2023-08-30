<?php

namespace App\Http\Controllers;

use App\DataTables\SubscriptionPackageDataTable;
use App\Models\Day;
use App\Models\Field;
use App\Models\Market;
use App\Models\PackageDay;
use App\Models\PackageDeliveryTime;
use App\Models\PackageOrder;
use App\Models\Product;
use App\Models\SubscriptionPackage;
use App\Repositories\DeliveryTimeRepository;
use App\Repositories\MarketRepository;
use App\Repositories\SubscriptionPackageRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Prettus\Validator\Exceptions\ValidatorException;
use Laracasts\Flash\Flash;

class SubscriptionPackageController extends Controller
{

    /** @var  SubscriptionPackageRepository */

    private $subscriptionPackageRepository;
    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /** @var  ProductRepository */
    private $productRepository;

    /** @var  MarketRepository */
    private $marketRepository;

    /** @var  DeliveryTimeRepository */
    private $deliveryTimeRepository;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(SubscriptionPackageRepository $packageRepo, CustomFieldRepository $customFieldRepo,
                                ProductRepository $productRepo, MarketRepository $marketRepo,
                                DeliveryTimeRepository $deliveryTimeRepo)
    {
        parent::__construct();
        $this->subscriptionPackageRepository = $packageRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->productRepository = $productRepo;
        $this->marketRepository = $marketRepo;
        $this->deliveryTimeRepository = $deliveryTimeRepo;

    }

    public function index(SubscriptionPackageDataTable $subscriptionPackageDataTable)
    {
        return $subscriptionPackageDataTable->render('packages.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $product =  $this->productRepository->pluck('base_name', 'id');

        $deliveryTimes =  $this->deliveryTimeRepository->pluck('name', 'id');
        $deliveryTimesSelected = [];
        $markets = Market::whereHas('fields', function ($query){
            $query->where('field_id', Field::FRESH_MILK);
        })->pluck('name','id');
        $hasCustomField = in_array($this->subscriptionPackageRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->subscriptionPackageRepository->model());
            $html = generateCustomField($customFields);
        }

        $packageDays = Day::pluck('name','id');
        $packageDaysSelected = [];

        return view('packages.create')->with("customFields", isset($html) ? $html : false)->with("product", $product)->with("markets", $markets)
                ->with("deliveryTimes", $deliveryTimes)
                ->with("deliveryTimesSelected",  $deliveryTimesSelected)
                ->with("packageDays", $packageDays)
                ->with("packageDaysSelected",  $packageDaysSelected);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();


        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->subscriptionPackageRepository->model());
        try {
            $package = $this->subscriptionPackageRepository->create($input);


            foreach($request->delivery_times as $deliveryTime){
                $package->package_delivery_times()->attach($package->id, ['package_id' => $package->id, 'delivery_time_id' => $deliveryTime]);
            }

            $package->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.package')]));

        return redirect(route('packages.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(SubscriptionPackage $package)
    {

        if (empty($package)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.package')]));
            return redirect(route('packages.index'));
        }

        $deliveryTimes =  $this->deliveryTimeRepository->pluck('name', 'id');
        $deliveryTimesSelected = $package->package_delivery_times()->pluck('delivery_times.id')->toArray();

        $markets = Market::whereHas('fields', function ($query){
            $query->where('field_id', Field::FRESH_MILK);
        })->pluck('name','id');

        $customFieldsValues = $package->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->subscriptionPackageRepository->model());
        $hasCustomField = in_array($this->subscriptionPackageRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        $packageDays = Day::pluck('name','id');
        $packageDaysSelected = $package->package_days()->pluck('days.id')->toArray();

        return view('packages.edit')->with('package', $package)->with("customFields", isset($html) ? $html : false)->with("markets", $markets)
            ->with("deliveryTimes",$deliveryTimes )
            ->with("deliveryTimesSelected", $deliveryTimesSelected)
            ->with("packageDays", $packageDays)
            ->with("packageDaysSelected",  $packageDaysSelected);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      //  $this->productRepository->pushCriteria(new ProductsOfUserCriteria(auth()->id()));
        $package = $this->subscriptionPackageRepository->findWithoutFail($id);

        if (empty($package)) {
            Flash::error('Package not found');
            return redirect(route('packages.index'));
        }
        $input = $request->all();

        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->subscriptionPackageRepository->model());
        try {
            $package = $this->subscriptionPackageRepository->update($input, $id);
            $packageDaysSelected = PackageDay::where('package_id',$id)->delete();

            if($input['package_days']) {
                foreach ($input['package_days'] as $package_day) {
                    PackageDay::create([
                        'package_id' => $id,
                        'day_id' => $package_day,
                    ]);
                }
            }
            $packageDeliveryTimeSelected = PackageDeliveryTime::where('package_id',$id)->delete();
            foreach($request->delivery_times as $deliveryTime){
                $package->package_delivery_times()->attach($package->id, ['package_id' => $package->id, 'delivery_time_id' => $deliveryTime]);
            }

            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $package->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.package')]));

        return redirect(route('packages.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!env('APP_DEMO', false)) {
            $package = $this->subscriptionPackageRepository->findWithoutFail($id);

            if (empty($package)) {
                Flash::error('Package not found');
                return redirect(route('packages.index'));
            }

            $packageOrders = PackageOrder::query()
                ->where('package_id', $id)
                ->get();

            if (count($packageOrders) > 0) {
                Flash::error('Package in use. Cannot delete this package');
                return redirect(route('packages.index'));
            }

           PackageDay::where('package_id', $id)->delete();
           PackageDeliveryTime::where('package_id',$id)->delete();

            $this->subscriptionPackageRepository->delete($id);

            Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.pacakge')]));

        } else {
            Flash::warning('This is only demo app you can\'t change this section ');
        }
        return redirect(route('packages.index'));
    }

    public function market_products(Request $request, $id){

        $marketID = $request->id;
        $products = Product::where('market_id', $marketID)
            ->where('product_type','!=', Product::VARIANT_BASE_PRODUCT)
            ->get();
        return json_encode($products);

    }
}
