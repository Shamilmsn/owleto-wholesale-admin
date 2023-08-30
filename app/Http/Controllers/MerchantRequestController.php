<?php

namespace App\Http\Controllers;

use App\DataTables\MerchantEnquiryDataTable;
use App\Models\MerchantRequest;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class MerchantRequestController extends Controller
{
    public function index(MerchantEnquiryDataTable $merchantEnquiryDataTable)
    {
        return $merchantEnquiryDataTable->render('merchant-requests.index');
    }

    public function store(Request $request)
    {
        $merchantRequest = MerchantRequest::find($request->merchantRequestId);
        $merchantRequest->status = $request->status;
        $merchantRequest->save();

        Flash::success(__('Status changed successfully'));

        return redirect()->route('merchant-requests.index');
    }
}
