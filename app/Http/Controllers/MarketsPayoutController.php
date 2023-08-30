<?php

namespace App\Http\Controllers;

use App\Criteria\Earnings\EarningOfMarketCriteria;
use App\Criteria\Markets\MarketsOfManagerCriteria;
use App\DataTables\MarketsPayoutDataTable;
use App\Http\Requests\CreateMarketsPayoutRequest;
use App\Http\Requests\UpdateMarketsPayoutRequest;
use App\Models\Earning;
use App\Models\MarketPayoutRequest;
use App\Models\MarketsPayout;
use App\Models\MarketTransaction;
use App\Repositories\CustomFieldRepository;
use App\Repositories\EarningRepository;
use App\Repositories\MarketRepository;
use App\Repositories\MarketsPayoutRepository;
use Carbon\Carbon;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class MarketsPayoutController extends Controller
{
    /** @var  MarketsPayoutRepository */
    private $marketsPayoutRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var MarketRepository
     */
    private $marketRepository;
    /**
     * @var EarningRepository
     */
    private $earningRepository;

    public function __construct(MarketsPayoutRepository $marketsPayoutRepo, CustomFieldRepository $customFieldRepo, MarketRepository $marketRepo, EarningRepository $earningRepository)
    {
        parent::__construct();
        $this->marketsPayoutRepository = $marketsPayoutRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->marketRepository = $marketRepo;
        $this->earningRepository = $earningRepository;
    }

    /**
     * Display a listing of the MarketsPayout.
     *
     * @param MarketsPayoutDataTable $marketsPayoutDataTable
     * @return Response
     */
    public function index(MarketsPayoutDataTable $marketsPayoutDataTable)
    {
        return $marketsPayoutDataTable->render('markets_payouts.index');
    }

    /**
     * Show the form for creating a new MarketsPayout.
     *
     * @return Response
     */
    public function create()
    {
        if(auth()->user()->hasRole('manager')){
            $this->marketRepository->pushCriteria(new MarketsOfManagerCriteria(auth()->id()));
        }
        $market = $this->marketRepository->pluck('name', 'id');

        $hasCustomField = in_array($this->marketsPayoutRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->marketsPayoutRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('markets_payouts.create')->with("customFields", isset($html) ? $html : false)->with("market", $market);
    }

    /**
     * Store a newly created MarketsPayout in storage.
     *
     * @param CreateMarketsPayoutRequest $request
     *
     * @return Response
     */
    public function store(CreateMarketsPayoutRequest $request)
    {
        $input = $request->all();

        if ($request->payout_request_id) {
            $payoutRequest = MarketPayoutRequest::findOrFail($request->payout_request_id);
            if ($input['amount'] > $payoutRequest->amount) {
                Flash::error('The payout amount must be less than requested amount');
                return redirect(route('market-payout-requests.show',$request->payout_request_id ))->withInput($input);
            }

            if ($payoutRequest->amount == $payoutRequest->paid_amount) {
                Flash::error('The requested amount paid already');
                return redirect(route('market-payout-requests.show',$request->payout_request_id ))->withInput($input);
            }
        }
        $earning = $this->earningRepository->findByField('market_id',$input['market_id'])->first();
        if($input['amount'] > $earning->market_balance){
            Flash::error('The payout amount must be less than market earning');
            return redirect(route('marketsPayouts.create'))->withInput($input);
        }
        $input['paid_date'] = Carbon::now();
        try {
            $marketsPayout = $this->marketsPayoutRepository->create($input);
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        $earning = Earning::where('market_id', $input['market_id'])->first();

        $balance = $earning->market_balance - $input['amount'];

        $marketTransaction = new MarketTransaction();
        $marketTransaction->market_id = $input['market_id'];
        $marketTransaction->debit = $input['amount'];
        $marketTransaction->balance = $balance;
        $marketTransaction->model()->associate($marketsPayout);
        $marketTransaction->save();

        $earning->market_balance = $balance;
        $earning->save();

        if ($request->payout_request_id) {
            $payoutRequest = MarketPayoutRequest::findOrFail($request->payout_request_id);
            if($input['amount'] == $payoutRequest->amount) {
                $payoutRequest->status = MarketPayoutRequest::PAID;
                $payoutRequest->paid_amount = $input['amount'];
            }
            else{
                if ($payoutRequest->paid_amount + $input['amount'] == $payoutRequest->amount) {
                    $payoutRequest->status = MarketPayoutRequest::PAID;
                }
                else {
                    $payoutRequest->status = MarketPayoutRequest::PARTIAL;
                }
                $payoutRequest->paid_amount = $payoutRequest->paid_amount + $input['amount'];
            }
            $payoutRequest->save();
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.markets_payout')]));

        return redirect(route('marketsPayouts.index'));
    }

    /**
     * Display the specified MarketsPayout.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $marketsPayout = $this->marketsPayoutRepository->findWithoutFail($id);

        if (empty($marketsPayout)) {
            Flash::error('Markets Payout not found');

            return redirect(route('marketsPayouts.index'));
        }

        return view('markets_payouts.show')->with('marketsPayout', $marketsPayout);
    }

    /**
     * Show the form for editing the specified MarketsPayout.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $marketsPayout = $this->marketsPayoutRepository->findWithoutFail($id);
        $market = $this->marketRepository->pluck('name', 'id');


        if (empty($marketsPayout)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.markets_payout')]));

            return redirect(route('marketsPayouts.index'));
        }
        $customFieldsValues = $marketsPayout->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->marketsPayoutRepository->model());
        $hasCustomField = in_array($this->marketsPayoutRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('markets_payouts.edit')->with('marketsPayout', $marketsPayout)->with("customFields", isset($html) ? $html : false)->with("market", $market);
    }

    /**
     * Update the specified MarketsPayout in storage.
     *
     * @param int $id
     * @param UpdateMarketsPayoutRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateMarketsPayoutRequest $request)
    {
        $marketsPayout = $this->marketsPayoutRepository->findWithoutFail($id);

        if (empty($marketsPayout)) {
            Flash::error('Markets Payout not found');
            return redirect(route('marketsPayouts.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->marketsPayoutRepository->model());
        try {
            $marketsPayout = $this->marketsPayoutRepository->update($input, $id);


            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $marketsPayout->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.markets_payout')]));

        return redirect(route('marketsPayouts.index'));
    }

    /**
     * Remove the specified MarketsPayout from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $marketsPayout = $this->marketsPayoutRepository->findWithoutFail($id);

        if (empty($marketsPayout)) {
            Flash::error('Markets Payout not found');

            return redirect(route('marketsPayouts.index'));
        }

        $this->marketsPayoutRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.markets_payout')]));

        return redirect(route('marketsPayouts.index'));
    }

    /**
     * Remove Media of MarketsPayout
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $marketsPayout = $this->marketsPayoutRepository->findWithoutFail($input['id']);
        try {
            if ($marketsPayout->hasMedia($input['collection'])) {
                $marketsPayout->getFirstMedia($input['collection'])->delete();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
