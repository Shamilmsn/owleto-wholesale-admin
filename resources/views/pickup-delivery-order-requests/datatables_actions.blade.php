<div class='btn-group btn-group-sm'>
{{--  @can('pickup-delivery-order-requests.edit')--}}
{{--    @if($attribute->status == 'PENDING')--}}
{{--      <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.accept')}}"--}}
{{--         href="{{ route('pickup-delivery-order-requests.edit',$attribute->id) }}" class='btn btn-link'>--}}
{{--          {{trans('lang.accept')}}--}}
{{--      </a>--}}
{{--        <a href="#" data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.reject')}}" data-order-request-id=" {{ $attribute->id }}"--}}
{{--           class='button-status-reject btn btn-link'>--}}
{{--            {{trans('lang.reject')}}--}}
{{--        </a>--}}
{{--    @endif--}}

{{--    @if($attribute->status == 'ACCEPTED')--}}
{{--            {{trans('lang.accepted')}}--}}
{{--    @elseif($attribute->status == 'REJECTED')--}}
{{--          {{trans('lang.rejected')}}--}}
{{--    @endif--}}

{{--  @endcan--}}
  @can('pickup-delivery-order-requests.show')
      <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.view_details')}}" href="{{ route('pickup-delivery-order-requests.show', $attribute->id) }}" class='btn btn-link'>
          <i class="fa fa-eye"></i>
      </a>
  @endcan

  @can('pickup-delivery-order-requests.destroy')
      {!! Form::open(['route' => ['pickup-delivery-order-requests.destroy', $attribute->id], 'method' => 'delete']) !!}
        {!! Form::button('<i class="fa fa-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-link text-danger',
        'onclick' => "return confirm('Are you sure?')"
        ]) !!}
      {!! Form::close() !!}
  @endcan
</div>
{{--<div class="modal fade" id="StatusRejectModal" tabindex="-1" role="dialog" aria-labelledby="StatusRejectModalLabel" aria-hidden="true">--}}
{{--    <div class="modal-dialog" role="document">--}}

{{--        <form id="status-reject-update"   class="p-5" method="POST" action="{{ route('pick_up_order_requests.reject', $attribute->id) }}" enctype="multipart/form-data">--}}
{{--            @csrf--}}
{{--            <div class="modal-content">--}}
{{--                <div class="modal-header">--}}
{{--                    <h5 class="modal-title" id="LiveTestModalLabel">Reject Order</h5>--}}
{{--                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">--}}
{{--                        <span aria-hidden="true">&times;</span>--}}
{{--                    </button>--}}
{{--                </div>--}}
{{--                <div class="modal-body">--}}
{{--                    <div class="row col-12" >--}}
{{--                        <label for="reject_reason" class="col-md-12 col-form-label">Reject Reason: </label>--}}
{{--                        <div class="col-md-12">--}}
{{--                            <div class="input-group mb-3">--}}
{{--                                <input type="hidden" name="pickup_order_request_Id" id="pickup_order_request_Id" value=" ">--}}
{{--                                <input type="text" name="rejected_reason"  required class="form-control @error('rejected_reason')--}}
{{--                                        is-invalid @enderror" id="reject_reason" placeholder="Reject Reason" value="" >--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                </div>--}}
{{--                <div class="modal-footer">--}}
{{--                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>--}}
{{--                    <button type="submit" class="btn btn-success">Update</button>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </form>--}}
{{--    </div>--}}
{{--</div>--}}

