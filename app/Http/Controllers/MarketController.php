<?php
/**
 * File name: MarketController.php
 * Last modified: 2020.04.30 at 08:21:08
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers;

use App\Criteria\Markets\MarketsOfUserCriteria;
use App\Criteria\Users\AdminsCriteria;
use App\Criteria\Users\ClientsCriteria;
use App\Criteria\Users\DriversCriteria;
use App\Criteria\Users\ManagersClientsCriteria;
use App\Criteria\Users\ManagersCriteria;
use App\DataTables\MarketDataTable;
use App\DataTables\RequestedMarketDataTable;
use App\Events\MarketChangedEvent;
use App\Http\Requests\CreateMarketRequest;
use App\Http\Requests\UpdateMarketRequest;
use App\Models\Category;
use App\Models\Market;
use App\Models\MarketPaymentMethod;
use App\Repositories\CategoryRepository;
use App\Repositories\CityRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\FieldRepository;
use App\Repositories\MarketRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class MarketController extends Controller
{
    /** @var  MarketRepository */
    private $marketRepository;

    /** @var  CityRepository */
    private $cityRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var FieldRepository
     */
    private $fieldRepository;
    /**
     * @var PaymentMethodRepository
     */
    private $paymentMethodRepository;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;


    public function __construct(MarketRepository $marketRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo,
                                UserRepository $userRepo, FieldRepository $fieldRepository,PaymentMethodRepository $paymentMethodRepo,
                                CityRepository $cityRepository, CategoryRepository $categoryRepository)
    {
        parent::__construct();
        $this->marketRepository = $marketRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->userRepository = $userRepo;
        $this->fieldRepository = $fieldRepository;
        $this->paymentMethodRepository = $paymentMethodRepo;
        $this->cityRepository = $cityRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Display a listing of the Market.
     *
     * @param MarketDataTable $marketDataTable
     * @return Response
     */
    public function index(MarketDataTable $marketDataTable)
    {
        return $marketDataTable->render('markets.index');
    }

    /**
     * Display a listing of the Market.
     *
     * @param MarketDataTable $marketDataTable
     * @return Response
     */
    public function requestedMarkets(RequestedMarketDataTable $requestedMarketDataTable)
    {
        return $requestedMarketDataTable->render('markets.requested');
    }

    /**
     * Show the form for creating a new Market.
     *
     * @return Response
     */
    public function create()
    {
        $user = $this->userRepository->getByCriteria(new ManagersCriteria())->pluck('name', 'id');
        $drivers = $this->userRepository->getByCriteria(new DriversCriteria())->pluck('name', 'id');
        $fields = $this->fieldRepository->pluck('name', 'id');
        $cities = $this->cityRepository->pluck('name', 'id');
        $paymetMethods = $this->paymentMethodRepository->where('is_active', true)->pluck('name', 'id');
//        $categories = $this->categoryRepository->where('parent_id',null)->pluck('name', 'id');
//        $categories = Category::where('parent_id',null)->pluck('name', 'id');

        $usersSelected = [];
        $driversSelected = [];
        $fieldsSelected = [];
        $paymetMethodsSelected = [];
        $categoriesSelected = [];

        $hasCustomField = in_array($this->marketRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->marketRepository->model());
            $html = generateCustomField($customFields);
        }

        return view('markets.create')->with("customFields", isset($html) ? $html : false)
            ->with("user", $user)
            ->with("cities", $cities)
            ->with("usersSelected", $usersSelected)
            ->with("drivers", $drivers)
            ->with("driversSelected", $driversSelected)
            ->with('fields', $fields)
            ->with('fieldsSelected', $fieldsSelected)
            ->with('paymetMethods', $paymetMethods)
            ->with('paymetMethodsSelected', $paymetMethodsSelected)
            ->with('categoriesSelected', $categoriesSelected);
    }

    /**
     * Store a newly created Market in storage.
     *
     * @param CreateMarketRequest $request
     *
     * @return Response
     */
    public function store(CreateMarketRequest $request)
    {
        $input = $request->all();
       // $input['area_id'] = $request->circle_id;

        if (auth()->user()->hasRole(['manager','client'])) {
            $input['users'] = [auth()->id()];
        }
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->marketRepository->model());
        try {
            $market = $this->marketRepository->create($input);

            $market->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($market, 'image');
            }
            if (isset($input['insta_profile_pic']) && $input['insta_profile_pic']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['insta_profile_pic']);
                $mediaItem = $cacheUpload->getMedia('insta_profile_pic')->first();

                $market = Market::findOrFail($market->id);
                $market->media_id_profile_pic = $mediaItem->id;
                $market->save();

                $mediaItem->copy($market, 'insta_profile_pic');
            }
            if (isset($input['insta_cover_pic']) && $input['insta_cover_pic']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['insta_cover_pic']);
                $mediaItem = $cacheUpload->getMedia('insta_cover_pic')->first();

                $market = Market::findOrFail($market->id);
                $market->media_id_cover_pic = $mediaItem->id;
                $market->save();

                $mediaItem->copy($market, 'insta_cover_pic');
            }
            event(new MarketChangedEvent($market, $market));

            if($request->paymetMethods){
                foreach($request->paymetMethods as $paymetMethod){
                    $market->market_payment_methods()->attach($market->id, ['market_id' => $market->id, 'payment_method_id' => $paymetMethod ]);
                }
            }
            if($request->categories){
                foreach($request->categories as $category){
                    $market->market_categories()->attach($market->id, ['market_id' => $market->id, 'category_id' => $category ]);
                }
            }

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.market')]));

        return redirect(route('markets.index'));
    }

    /**
     * Display the specified Market.
     *
     * @param int $id
     *
     * @return Response
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function show($id)
    {
        $this->marketRepository->pushCriteria(new MarketsOfUserCriteria(auth()->id()));
        $market = $this->marketRepository->findWithoutFail($id);

        if (empty($market)) {
            Flash::error('Market not found');

            return redirect(route('markets.index'));
        }

        return view('markets.show')->with('market', $market);
    }

    /**
     * Show the form for editing the specified Market.
     *
     * @param int $id
     *
     * @return Response
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function edit($id)
    {
        $this->marketRepository->pushCriteria(new MarketsOfUserCriteria(auth()->id()));
        $market = $this->marketRepository->findWithoutFail($id);
        $cities = $this->cityRepository->pluck('name', 'id');

        if (empty($market)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.market')]));
            return redirect(route('markets.index'));
        }
        if($market['active'] == 0){
            $user = $this->userRepository->getByCriteria(new ManagersClientsCriteria())->pluck('name', 'id');
        } else {
            $user = $this->userRepository->getByCriteria(new ManagersCriteria())->pluck('name', 'id');
        }

        $drivers = $this->userRepository->getByCriteria(new DriversCriteria())->pluck('name', 'id');
        $fields = $this->fieldRepository->pluck('name', 'id');
        $paymetMethods = $this->paymentMethodRepository->where('is_active', true)->pluck('name', 'id');
        $categories = Category::where('parent_id',null)->pluck('name', 'id');


        $usersSelected = $market->users()->pluck('users.id')->toArray();
        $driversSelected = $market->drivers()->pluck('users.id')->toArray();
        $fieldsSelected = $market->fields()->pluck('fields.id', 'fields.name')->toArray();
        $categoriesSelected = $market->market_categories()->pluck('categories.id','categories.name')->toArray();
        $categoriesIdsSelected = $market->market_categories()->pluck('categories.id')->toArray();

        $paymetMethodsSelected = $market->market_payment_methods()->pluck('payment_methods.id')->toArray();

        $customFieldsValues = $market->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->marketRepository->model());
        $hasCustomField = in_array($this->marketRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('markets.edit')->with('market', $market)->with("customFields", isset($html) ? $html : false)
            ->with("user", $user)
            ->with("cities", $cities)
            ->with("usersSelected", $usersSelected)
            ->with("drivers", $drivers)->with("driversSelected", $driversSelected)
            ->with('fields', $fields)->with('fieldsSelected', $fieldsSelected)
            ->with('paymetMethods', $paymetMethods)->with('paymetMethodsSelected', $paymetMethodsSelected)
            ->with('categoriesSelected', $categoriesSelected)
            ->with('categories', $categories)
            ->with('categoriesIdsSelected', $categoriesIdsSelected);


    }

    /**
     * Update the specified Market in storage.
     *
     * @param int $id
     * @param UpdateMarketRequest $request
     *
     * @return Response
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function update($id, UpdateMarketRequest $request)
    {
        $this->marketRepository->pushCriteria(new MarketsOfUserCriteria(auth()->id()));
        $oldMarket = $this->marketRepository->findWithoutFail($id);

        if (empty($oldMarket)) {
            Flash::error('Market not found');
            return redirect(route('markets.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->marketRepository->model());
        try {

            $market = $this->marketRepository->update($input, $id);

            if (isset($input['image']) && $input['image']) {
                $market->clearMediaCollection('image');
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($market, 'image');
            }

            if (isset($input['insta_profile_pic']) && $input['insta_profile_pic']) {
                $market->clearMediaCollection('insta_profile_pic');
                $cacheUpload = $this->uploadRepository->getByUuid($input['insta_profile_pic']);
                $mediaItem = $cacheUpload->getMedia('insta_profile_pic')->first();

                $market = Market::findOrFail($id);
                $market->media_id_profile_pic = $mediaItem->id;
                $market->save();

                $mediaItem->copy($market, 'insta_profile_pic');
            }
            if (isset($input['insta_cover_pic']) && $input['insta_cover_pic']) {
                $market->clearMediaCollection('insta_cover_pic');
                $cacheUpload = $this->uploadRepository->getByUuid($input['insta_cover_pic']);
                $mediaItem = $cacheUpload->getMedia('insta_cover_pic')->first();

                $market = Market::findOrFail($id);
                $market->media_id_cover_pic = $mediaItem->id;
                $market->save();

                $mediaItem->copy($market, 'insta_cover_pic');
            }

            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $market->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }

            $paymetMethods = $request->paymetMethods;
            $market->market_payment_methods()->detach($market->paymetMethods);
            foreach($request->paymetMethods as $paymetMethod){
                $market->market_payment_methods()->attach($market->id, ['market_id' => $market->id, 'payment_method_id' => $paymetMethod]);
            }

            $categories = $request->categories;
            $market->market_categories()->detach($market->categories);
            foreach($request->categories as $category){
                $market->market_categories()->attach($market->id, ['market_id' => $market->id, 'category_id' => $category]);
            }

            event(new MarketChangedEvent($market, $oldMarket));
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.market')]));

        return redirect(route('markets.index'));
    }

    /**
     * Remove the specified Market from storage.
     *
     * @param int $id
     *
     * @return Response
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function destroy($id)
    {
        if (!env('APP_DEMO', false)) {
            $this->marketRepository->pushCriteria(new MarketsOfUserCriteria(auth()->id()));
            $market = $this->marketRepository->findWithoutFail($id);

            if (empty($market)) {
                Flash::error('Market not found');

                return redirect(route('markets.index'));
            }

            $marketPaymentMethods = MarketPaymentMethod::where('market_id', $id)->delete();

            $this->marketRepository->delete($id);

            Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.market')]));
        } else {
            Flash::warning('This is only demo app you can\'t change this section ');
        }
        return redirect(route('markets.index'));
    }

    /**
     * Remove Media of Market
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $market = $this->marketRepository->findWithoutFail($input['id']);
        try {
            if ($market->hasMedia($input['collection'])) {
                $market->getFirstMedia($input['collection'])->delete();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
    public function market_categories(Request $request)
    {
        $selectedfields = $request->sectorIds;

        if (! $request->sectorIds){
            $selectedfields = [];
        }

        $primary_sector_id = $request->primary_sector_id;

        array_push($selectedfields, $primary_sector_id);

        $categories = $this->categoryRepository
            ->whereIn('field_id', $selectedfields)
            ->where('parent_id', null)
            ->get();

        return json_encode($categories);
    }
}
