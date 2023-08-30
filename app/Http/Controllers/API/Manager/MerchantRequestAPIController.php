<?php

namespace App\Http\Controllers\API\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\MerchantStoreRequest;
use App\Models\MerchantRequest;
use App\Repositories\MerchantRequestRepository;

class MerchantRequestAPIController extends Controller
{
    /** @var  MerchantRequestRepository */
    private $merchantRequestRepository;

    public function __construct(MerchantRequestRepository $merchantRequestRepository)
    {
        parent::__construct();
        $this->merchantRequestRepository = $merchantRequestRepository;
    }

    public function store(MerchantStoreRequest $request)
    {
        $input = $request->all();
        $input['status'] = MerchantRequest::PENDING_STATUS;
        $merchantRequest = $this->merchantRequestRepository->create($input);

        return $this->sendResponse($merchantRequest, 'Inserted Successfully');
    }
}
