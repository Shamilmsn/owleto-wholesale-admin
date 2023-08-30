<?php

namespace App\Http\Controllers;

use App\Criteria\Earnings\EarningOfMarketCriteria;
use App\Criteria\Users\DriversCriteria;
use App\Criteria\Users\FilterByUserCriteria;
use App\DataTables\DriversPayoutDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateDriversPayoutRequest;
use App\Http\Requests\UpdateDriversPayoutRequest;
use App\Models\DriverPayoutRequest;
use App\Models\DriverTransaction;
use App\Models\MarketTransaction;
use App\Repositories\DriverRepository;
use App\Repositories\DriversPayoutRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Flash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class DriversPayoutController extends Controller
{
    /** @var  DriversPayoutRepository */
    private $driversPayoutRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
  * @var UserRepository
  */
private $userRepository;
    /**
     * @var DriverRepository
     */
    private $driverRepository;

    public function __construct(DriversPayoutRepository $driversPayoutRepo, DriverRepository $driverRepository, CustomFieldRepository $customFieldRepo , UserRepository $userRepo)
    {
        parent::__construct();
        $this->driversPayoutRepository = $driversPayoutRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->userRepository = $userRepo;
        $this->driverRepository = $driverRepository;
    }

    /**
     * Display a listing of the DriversPayout.
     *
     * @param DriversPayoutDataTable $driversPayoutDataTable
     * @return Response
     */
    public function index(DriversPayoutDataTable $driversPayoutDataTable)
    {
        return $driversPayoutDataTable->render('drivers_payouts.index');
    }

    /**
     * Show the form for creating a new DriversPayout.
     *
     * @return Response
     */
    public function create()
    {

        $this->userRepository->pushCriteria(new DriversCriteria());

        $usersWithEarning = $this->driverRepository->where('balance' ,'>', 0)->pluck('user_id')->toArray();

        $user = $this->userRepository->with('driver')->pluck('name','id');

        
        $hasCustomField = in_array($this->driversPayoutRepository->model(),setting('custom_field_models',[]));
            if($hasCustomField){
                $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->driversPayoutRepository->model());
                $html = generateCustomField($customFields);
            }

        return view('drivers_payouts.create')->with("customFields", isset($html) ? $html : false)->with("user",$user);
    }

    /**
     * Store a newly created DriversPayout in storage.
     *
     * @param CreateDriversPayoutRequest $request
     *
     * @return Response
     */
    public function driverPayoutStore(CreateDriversPayoutRequest $request)
    {
        $input = $request->all();

        return $input;

        $input['paid_date'] = Carbon::now();
        $driverEarning = $this->driverRepository->first();

        $payoutRequest = DriverPayoutRequest::findOrFail($request->payout_request_id);
        if ($input['amount'] > $payoutRequest->amount) {
            Flash::error('The payout amount must be less than requested amount');
            return redirect(route('market-payout-requests.show',$request->payout_request_id ))->withInput($input);
        }

        if ($payoutRequest->amount == $payoutRequest->paid_amount) {
            Flash::error('The requested amount paid already');
            return redirect(route('market-payout-requests.show',$request->payout_request_id ))->withInput($input);
        }

        if (!$driverEarning) {
            Flash::error('There is no payout for this market');
            return redirect()->back()->withInput($input);
        }

        if($input['amount'] > $driverEarning->balance){
            Flash::error('The payout amount must be less than driver earning');
            return redirect()->back()->withInput($input);
        }
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->driversPayoutRepository->model());
        try {
            $this->driverRepository->update(['balance'=>$driverEarning->balance - $input['amount']], $driverEarning->id);
            $driversPayout = $this->driversPayoutRepository->create($input);
            $driversPayout->customFieldsValues()->createMany(getCustomFieldsValues($customFields,$request));

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        $totalDebit = $driverEarning->balance - $input['amount'];

        $driverTransaction = new DriverTransaction();
        $driverTransaction->user_id = $driverEarning->user_id;
        $driverTransaction->type = DriverTransaction::TYPE_DEBIT;
        $driverTransaction->description = 'Amount Credited';
        $driverTransaction->debit = $input['amount'];
        $driverTransaction->balance =$totalDebit;
        $driverTransaction->model()->associate($driversPayout);
        $driverTransaction->save();

        Flash::success(__('lang.saved_successfully',['operator' => __('lang.drivers_payout')]));

        return redirect(route('driversPayouts.index'));
    }


    public function store(CreateDriversPayoutRequest $request)
    {

        $input = $request->all();

        $driverRequestId = $request->driverRequestId;

        $driverRequest = DriverPayoutRequest::findorFail($driverRequestId);

        $input['paid_date'] = Carbon::now();

        $this->driverRepository->pushCriteria(new FilterByUserCriteria($input['user_id']));
        $driverEarning = $this->driverRepository->first();

        if($input['amount'] > $driverEarning->balance){
            Flash::error('The payout amount must be less than driver earning');
            return redirect()->back()->withInput($input);
        }

        if($driverRequest->paid_amount) {
            $balance_amount = $driverRequest->amount - $driverRequest->paid_amount;
            if($input['amount'] > $balance_amount) {

                Flash::error('The amount should not be greater than '.$balance_amount);
                return redirect()->back()->withInput($input);
            }
        }


        $totalPaidAmount = $request->amount + $driverRequest->paid_amount;

        if($totalPaidAmount == $driverRequest->amount) {
            $driverRequest->status =  'PAID';
            $driverRequest->paid_amount = $totalPaidAmount;
        }

        if($request->amount > $driverRequest->amount) {

            Flash::error('The amount should not be graterthan '. $driverRequest->amount);
            return redirect()->back()->withInput($input);

        }else if($request->amount < $driverRequest->amount && $totalPaidAmount < $driverRequest->amount) {
            $driverRequest->status =  'PARTIALY PAID';

        }else{
            $driverRequest->status =  'PAID';

        }
        $driverRequest->paid_amount = $totalPaidAmount;
        $driverRequest->save();

        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->driversPayoutRepository->model());
        try {
            $this->driverRepository->update(['balance'=>$driverEarning->balance - $input['amount']], $driverEarning->id);
            $driversPayout = $this->driversPayoutRepository->create($input);
            $driversPayout->customFieldsValues()->createMany(getCustomFieldsValues($customFields,$request));
            
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        $totalCredit = DriverTransaction::where('user_id', $driverEarning->user_id)->sum('credit');
        $totalDebit = DriverTransaction::where('user_id', $driverEarning->user_id)->sum('debit');

        $totalDebit = $totalDebit + $input['amount'];

        $driverTransaction = new DriverTransaction();
        $driverTransaction->user_id = $driverEarning->user_id;
        $driverTransaction->type = DriverTransaction::TYPE_DEBIT;
        $driverTransaction->description = 'Amount Credited';
        $driverTransaction->debit = $input['amount'];
        $driverTransaction->balance = $totalCredit - $totalDebit;
        $driverTransaction->model()->associate($driversPayout);
        $driverTransaction->save();

        Flash::success(__('lang.saved_successfully',['operator' => __('lang.drivers_payout')]));

        return redirect(route('driversPayouts.index'));
    }

    /**
     * Display the specified DriversPayout.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $driversPayout = $this->driversPayoutRepository->findWithoutFail($id);

        if (empty($driversPayout)) {
            Flash::error('Drivers Payout not found');

            return redirect(route('driversPayouts.index'));
        }

        return view('drivers_payouts.show')->with('driversPayout', $driversPayout);
    }

    /**
     * Show the form for editing the specified DriversPayout.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        return 1;
        $driversPayout = $this->driversPayoutRepository->findWithoutFail($id);
        $user = $this->userRepository->pluck('name','id');
        

        if (empty($driversPayout)) {
            Flash::error(__('lang.not_found',['operator' => __('lang.drivers_payout')]));

            return redirect(route('driversPayouts.index'));
        }
        $customFieldsValues = $driversPayout->customFieldsValues()->with('customField')->get();
        $customFields =  $this->customFieldRepository->findByField('custom_field_model', $this->driversPayoutRepository->model());
        $hasCustomField = in_array($this->driversPayoutRepository->model(),setting('custom_field_models',[]));
        if($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('drivers_payouts.edit')->with('driversPayout', $driversPayout)->with("customFields", isset($html) ? $html : false)->with("user",$user);
    }

    /**
     * Update the specified DriversPayout in storage.
     *
     * @param  int              $id
     * @param UpdateDriversPayoutRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateDriversPayoutRequest $request)
    {
        $driversPayout = $this->driversPayoutRepository->findWithoutFail($id);

        if (empty($driversPayout)) {
            Flash::error('Drivers Payout not found');
            return redirect(route('driversPayouts.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->driversPayoutRepository->model());
        try {
            $driversPayout = $this->driversPayoutRepository->update($input, $id);
            
            
            foreach (getCustomFieldsValues($customFields, $request) as $value){
                $driversPayout->customFieldsValues()
                    ->updateOrCreate(['custom_field_id'=>$value['custom_field_id']],$value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully',['operator' => __('lang.drivers_payout')]));

        return redirect(route('driversPayouts.index'));
    }

    /**
     * Remove the specified DriversPayout from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $driversPayout = $this->driversPayoutRepository->findWithoutFail($id);

        if (empty($driversPayout)) {
            Flash::error('Drivers Payout not found');

            return redirect(route('driversPayouts.index'));
        }

        $this->driversPayoutRepository->delete($id);

        Flash::success(__('lang.deleted_successfully',['operator' => __('lang.drivers_payout')]));

        return redirect(route('driversPayouts.index'));
    }

        /**
     * Remove Media of DriversPayout
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $driversPayout = $this->driversPayoutRepository->findWithoutFail($input['id']);
        try {
            if($driversPayout->hasMedia($input['collection'])){
                $driversPayout->getFirstMedia($input['collection'])->delete();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
