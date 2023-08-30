@if($merchantRequest->status == \App\Models\MerchantRequest::PENDING_STATUS)
    <a href="#" class="change-status" data-id="{{ $merchantRequest->id }}"> change status </a>
@endif