<?php

namespace App\Http\Controllers;

use App\Models\OrderRequest;
use App\Repositories\OrderRequestCartRepository;
use App\Repositories\CustomFieldRepository;

use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class OrderRequestCartController extends Controller
{
    /** @var  OrderRequestCartRepository */
    private $orderRequestCartRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    public function __construct(OrderRequestCartRepository $orderRequestCartRepo, CustomFieldRepository $customFieldRepo )
    {
        parent::__construct();
        $this->orderRequestCartRepository = $orderRequestCartRepo;
        $this->customFieldRepository = $customFieldRepo;

    }

    public function store(Request $request)
    {
        $input = $request->all();
        $orderRequest  = OrderRequest::findOrfail($request->order_request_id);
        $input['user_id'] = $orderRequest->user_id;

        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->orderRequestCartRepository->model());
        try {
            $orderStatus = $this->orderRequestCartRepository->create($input);
            $orderStatus->customFieldsValues()->createMany(getCustomFieldsValues($customFields,$request));

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully',['operator' => __('lang.cart')]));

        return redirect(route('orderRequests.show', $request->order_request_id));
    }

    public function destroy($id)
    {
        $orderRequestCart = $this->orderRequestCartRepository->findWithoutFail($id);

        if (empty($orderRequestCart)) {
            Flash::error('Carted Item not found');

            return redirect(route('orderRequests.index'));
        }

        $this->orderRequestCartRepository->delete($id);

        Flash::success(__('lang.deleted_successfully',['operator' => __('lang.cart')]));

        return redirect()->back();
    }

}
