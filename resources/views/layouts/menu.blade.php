@can('dashboard')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('dashboard*') ? 'active' : '' }}" href="{!! url('dashboard') !!}">@if($icons)
                <i class="nav-icon fa fa-dashboard"></i>@endif
            <p>{{trans('lang.dashboard')}}</p></a>
    </li>
@endcan

@can('deliveryTypes.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('deliveryTypes*') ? 'active' : '' }}" href="{!! route('deliveryTypes.index') !!}">@if($icons)<i class="nav-icon fa fa-tasks"></i>@endif<p>{{trans('lang.delivery_type_plural')}}</p></a>
    </li>
@endcan

@can('fields.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('fields*') ? 'active' : '' }}" href="{!! route('fields.index') !!}">@if($icons)<i class="nav-icon fa fa-tasks"></i>@endif<p>{{trans('lang.field_plural')}}</p></a>
    </li>
@endcan

@can('markets.index')
    <li class="nav-item has-treeview {{ (Request::is('markets*') || Request::is('requestedMarkets*') || Request::is('galleries*') || Request::is('marketReviews*')) && !Request::is('marketsPayouts*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ (Request::is('markets*') || Request::is('requestedMarkets*') || Request::is('galleries*') || Request::is('marketReviews*')) && !Request::is('marketsPayouts*')? 'active' : '' }}"> @if($icons)
                <i class="nav-icon fa fa-shopping-basket"></i>@endif
            <p>{{trans('lang.market_plural')}} <i class="right fa fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            @can('markets.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('markets*') ? 'active' : '' }}" href="{!! route('markets.index') !!}">@if($icons)
                            <i class="nav-icon fa fa-reorder"></i>@endif<p>{{trans('lang.market_plural')}}</p></a>
                </li>
            @endcan
            @can('galleries.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('galleries*') ? 'active' : '' }}" href="{!! route('galleries.index') !!}">@if($icons)
                            <i class="nav-icon fa fa-image"></i>@endif<p>{{trans('lang.gallery_plural')}}</p></a>
                </li>
            @endcan
            @can('marketReviews.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('marketReviews*') ? 'active' : '' }}" href="{!! route('marketReviews.index') !!}">@if($icons)
                            <i class="nav-icon fa fa-comments"></i>@endif<p>{{trans('lang.market_review_plural')}}</p></a>
                </li>
            @endcan
        </ul>
    </li>
@endcan

@can('categories.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('categories*') ? 'active' : '' }}" href="{!! route('categories.index') !!}">@if($icons)
                <i class="nav-icon fa fa-folder"></i>@endif<p>{{trans('lang.category_plural')}}</p></a>
    </li>
@endcan
@can('cities.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('cities*') ? 'active' : '' }}" href="{!! route('cities.index') !!}">@if($icons)
                <i class="nav-icon fa fa-address-card"></i>@endif<p>{{trans('lang.city_plural')}}</p></a>
    </li>
@endcan
@can('areas.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('areas*') ? 'active' : '' }}" href="{!! route('areas.index') !!}">@if($icons)
                <i class="nav-icon fa fa-clipboard"></i>@endif<p>{{trans('lang.area_plural')}}</p></a>
    </li>
@endcan


@can('products.index')
    <li class="nav-item has-treeview {{ Request::is('products*') ||
          Request::is('attributes*') ||
          Request::is('attributeOptions*') ||
           Request::is('options*') ||
           Request::is('optionGroups*') ||
           Request::is('productReviews*') ||
            Request::is('product-approvals*') ||
              Request::is('flash-sales*') ||
             Request::is('nutrition*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{  Request::is('products*') ||
         Request::is('attributes*') ||
          Request::is('attributeOptions*') ||
           Request::is('options*') ||
           Request::is('optionGroups*') ||
           Request::is('productReviews*') ||
           Request::is('product-approvals*') ||
           Request::is('flash-sales*') ||
           Request::is('nutrition*') ? 'active' : '' }}"> @if($icons)
                <i class="nav-icon fa fa-archive"></i>@endif
            <p>{{trans('lang.product_plural')}} <i class="right fa fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            @can('products.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('products*') ? 'active' : '' }}" href="{!! route('products.index') !!}">@if($icons)
                            <i class="nav-icon fa fa-archive"></i>@endif
                        <p>{{trans('lang.product_plural')}}</p></a>
                </li>
            @endcan
            @can('attributes.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('attributes*') ? 'active' : '' }}" href="{!! route('attributes.index') !!}">@if($icons)<i class="nav-icon fa fa-plus-square"></i>@endif<p>{{trans('lang.attribute_plural')}}</p></a>
                </li>
            @endcan
            @can('attributeOptions.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('attributeOptions*') ? 'active' : '' }}" href="{!! route('attributeOptions.index') !!}">@if($icons)<i class="nav-icon fa fa-plus-square"></i>@endif<p>{{trans('lang.attribute_option_plural')}}</p></a>
                </li>
            @endcan
{{--            @can('optionGroups.index')--}}
{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link {{ Request::is('optionGroups*') ? 'active' : '' }}" href="{!! route('optionGroups.index') !!}">@if($icons)<i class="nav-icon fa fa-plus-square"></i>@endif<p>{{trans('lang.option_group_plural')}}</p></a>--}}
{{--                </li>--}}
{{--            @endcan--}}
{{--            @can('options.index')--}}
{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link {{ Request::is('options*') ? 'active' : '' }}" href="{!! route('options.index') !!}">@if($icons)--}}
{{--                            <i class="nav-icon fa fa-plus-square-o"></i>@endif<p>{{trans('lang.option_plural')}}</p></a>--}}
{{--                </li>--}}
{{--            @endcan--}}

            @can('productReviews.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('productReviews*') ? 'active' : '' }}" href="{!! route('productReviews.index') !!}">@if($icons)
                            <i class="nav-icon fa fa-comments"></i>@endif<p>{{trans('lang.product_review_plural')}}</p></a>
                </li>
            @endcan

            @can('product-approvals.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('product-approvals*') ? 'active' : '' }}"
                       href="{!! route('product-approvals.index') !!}">
                        @if($icons)
                            <i class="nav-icon fa fa-archive"></i>
                        @endif
                        <p>Product Approvals</p>
                    </a>
                </li>
            @endcan
                @can('flash-sales.index')
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('flash-sales*') ? 'active' : '' }}"
                           href="{!! route('flash-sales.index') !!}">
                            @if($icons)
                                <i class="nav-icon fa fa-archive"></i>
                            @endif
                            <p>Flash Sales</p>
                        </a>
                    </li>
                @endcan
        </ul>
    </li>
@endcan
@can('packages.index')
<li class="nav-item">
    <a class="nav-link {{ Request::is('packages*') ? 'active' : '' }}" href="{!! route('packages.index') !!}">@if($icons)
            <i class="nav-icon fa fa-book"></i>@endif<p>{{trans('lang.package_plural')}}</p></a>
</li>
@endcan

@can('orders.index')
    <li class="nav-item has-treeview {{
    Request::is('orders*') ||
    Request::is('orderStatuses*') ||
    Request::is('orderRequests*') ||
    Request::is('deliveryAddresses*') ||
    Request::is('package-orders*') ||
    Request::is('order-request-orders*') ||
    Request::is('todays-package-orders*') ||
    Request::is('pickup-orders*') ||
    Request::is('deliver-orders*') ||
    Request::is('return-requests*') ||
    Request::is('express-orders*') ||
    Request::is('home-bakers-orders*') ||
    Request::is('pickup-delivery-orders*')? 'menu-open' : '' }}">

        <a href="#" class="nav-link {{
            Request::is('orders*') ||
            Request::is('orderStatuses*') ||
            Request::is('orderRequests*')||
            Request::is('order-request-orders*') ||
            Request::is('deliveryAddresses*') ||
            Request::is('todays-package-orders*')||
            Request::is('return-requests*') ||
            Request::is('package-orders*') ||
            Request::is('pickup-orders*') ||
            Request::is('deliver-orders*') ||
            Request::is('express-orders*') ||
            Request::is('home-bakers-orders*') ||
            Request::is('pickup-delivery-orders*')? 'active' : '' }}"> @if($icons)
                <i class="nav-icon fa fa-shopping-bag"></i>@endif
            <p>{{trans('lang.order_plural')}} <i class="right fa fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            @can('orders.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('orders*') ? 'active' : '' }}"
                       href="{!! route('orders.index') !!}">
                        @if($icons)
                            <i class="nav-icon fa fa-shopping-bag"></i>
                        @endif
                        <p>{{trans('lang.order_plural')}}</p>
                            @if(getPendingOrdersCount()>0) <span class="badge badge-pill badge-success driver-request-badge" id="pending_order_count">{{ getPendingOrdersCount() }}</span>@endif

                    </a>
                </li>
            @endcan
            @can('express-orders.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('express-orders*') ? 'active' : '' }}"
                       href="{!! route('express-orders.index') !!}">
                        @if($icons)
                            <i class="nav-icon fa fa-shopping-bag"></i>
                        @endif
                        <p>Express Orders</p>
                    </a>
                </li>
            @endcan
            @can('home-bakers-orders.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('home-bakers-orders*') ? 'active' : '' }}"
                       href="{!! route('home-bakers-orders.index') !!}">
                        @if($icons)
                            <i class="nav-icon fa fa-shopping-bag"></i>
                        @endif
                        <p>Home Bakers Orders</p>
                    </a>
                </li>
            @endcan
                <li class="nav-item has-treeview {{
                    Request::is('pickup-orders*') ||
                    Request::is('deliver-orders*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{
                        Request::is('pickup-orders*') ||
                        Request::is('deliver-orders*') ? 'active' : '' }}">
                        @if($icons)
                            <i class="nav-icon fa fa-shopping-bag"></i>@endif
                        <p>Sloted Orders <i class="right fa fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('pickup-orders.index')
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('pickup-orders*') ? 'active' : '' }}"
                                   href="{!! route('pickup-orders.index') !!}">
                                    @if($icons)
                                        <i class="nav-icon fa fa-shopping-bag"></i>
                                    @endif
                                    <p>{{trans('lang.permission_pickup-orders')}}</p>
                                </a>
                            </li>
                        @endcan
                        @can('deliver-orders.index')
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('deliver-orders*') ? 'active' : '' }}"
                                   href="{!! route('deliver-orders.index') !!}">
                                    @if($icons)
                                        <i class="nav-icon fa fa-shopping-bag"></i>
                                    @endif
                                    <p>{{trans('lang.permission_deliver-orders')}}</p>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @can('package-orders.index')
                @if(isMerchantHascription())
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('package-orders*') ? 'active' : '' }}"
                           href="{!! route('package-orders.index') !!}">
                            @if($icons)
                                <i class="nav-icon fa fa-shopping-bag"></i>
                            @endif
                            <p>{{trans('lang.package_order_plural')}}</p></a>
                    </li>
                @endif
            @endcan
                @if(isMerchantHasManualOrders())
                    @can('order-request-orders.index')
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('order-request-orders*') ? 'active' : '' }}"
                               href="{!! route('order-request-orders.index') !!}">@if($icons)
                                    <i class="nav-icon fa fa-shopping-bag"></i>@endif
                                <p>{{trans('lang.order_request_order_plural')}}</p></a>
                        </li>
                    @endcan
                @endif
                @if(request()->user()->hasRole('admin'))
                    @can('pickup-delivery-orders.index')
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('pickup-delivery-orders*') ? 'active' : '' }}" href="{!! route('pickup-delivery-orders.index') !!}">@if($icons)
                                    <i class="nav-icon fa fa-shopping-bag"></i>@endif<p>{{trans('lang.pickup_delivery_order_plural')}}</p></a>
                        </li>
                    @endcan
                @endif

                @if(isMerchantHascription())
{{--                    @can('todays-package-orders.index')--}}
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('todays-package-orders*') ? 'active' : '' }}" href="{!! route('todays-package-orders.index') !!}">@if($icons)
                                    <i class="nav-icon fa fa-shopping-bag"></i>@endif<p>Today's package orders</p></a>
                        </li>
{{--                    @endcan--}}
                @endif

            @can('orderStatuses.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('orderStatuses*') ? 'active' : '' }}" href="{!! route('orderStatuses.index') !!}">@if($icons)
                            <i class="nav-icon fa fa-server"></i>@endif<p>{{trans('lang.order_status_plural')}}</p></a>
                </li>
            @endcan

            @can('orderRequests.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('orderRequests*') ? 'active' : '' }}" href="{!! route('orderRequests.index') !!}">
                        @if($icons)
                            <i class="nav-icon fa fa-server"></i>
                        @endif
                        <p>{{trans('lang.order_request_plural')}}</p>
                    </a>
                </li>
            @endcan

            @can('return-requests.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('return-requests*') ? 'active' : '' }}" href="{!! route('return-requests.index') !!}">
                        @if($icons)
                            <i class="nav-icon fa fa-server"></i>
                        @endif
                        <p>Return Requests</p>
                    </a>
                </li>
            @endcan

{{--            @can('deliveryAddresses.index')--}}
{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link {{ Request::is('deliveryAddresses*') ? 'active' : '' }}" href="{!! route('deliveryAddresses.index') !!}">@if($icons)<i class="nav-icon fa fa-map"></i>@endif<p>{{trans('lang.delivery_address_plural')}}</p></a>--}}
{{--                </li>--}}
{{--            @endcan--}}

        </ul>
    </li>
@endcan

@can('coupons.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('coupons*') ? 'active' : '' }}" href="{!! route('coupons.index') !!}">@if($icons)<i class="nav-icon fa fa-ticket"></i>@endif<p>{{trans('lang.coupon_plural')}} </p></a>
    </li>
@endcan


@can('drivers.index')
    <li class="nav-item has-treeview {{ Request::is('drivers*') || Request::is('driver-reviews*') || Request::is('driver-requests*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ Request::is('drivers*') || Request::is('driver-reviews*') || Request::is('driver-requests*') ? 'active' : '' }}"> @if($icons)
                <i class="nav-icon fa fa-car"></i>@endif
            <p> Drivers <i class="right fa fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
            @can('drivers.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('drivers*') ? 'active' : '' }}" href="{!! route('drivers.index') !!}">
                        @if($icons)<i class="nav-icon fa fa-car"></i>@endif<p>{{trans('lang.driver_plural')}} </p></a>
                </li>
            @endcan

            @can('driver-reviews.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('driver-reviews*') ? 'active' : '' }}" href="{!! route('driver-reviews.index') !!}">@if($icons)<i class="nav-icon fa fa-car"></i>@endif<p>Driver Reviews </p></a>
                </li>
            @endcan

            @can('driver-requests.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('driver-requests*') ? 'active' : '' }}" href="{!! route('driver-requests.index') !!}">
                        @if($icons)<i class="nav-icon fa fa-car"></i>@endif<p>{{trans('lang.requested_drivers')}} </p>

                        @if(getDriverRequestCount()>0) <span class="badge badge-pill badge-success driver-request-badge">{{ getDriverRequestCount() }}</span>@endif
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcan

@can('merchant-requests.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('merchant-requests*') ? 'active' : '' }}" href="{!! route('merchant-requests.index') !!}">
            @if($icons)<i class="nav-icon fa fa-car"></i>@endif<p>Merchant Enquiry</p>
                @if(getMerchantRequestCount()>0) <span class="badge badge-pill badge-success driver-request-badge">{{ getMerchantRequestCount() }}</span>@endif

        </a>
    </li>
@endcan

@can('compliant.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('compliant*') ? 'active' : '' }}" href="{!! route('compliant.index') !!}">
            @if($icons)<i class="nav-icon fa fa-car"></i>@endif<p>Complaint</p>
        </a>
    </li>
@endcan

@can('faqs.index')
    <li class="nav-item has-treeview {{ Request::is('faqCategories*') || Request::is('faqs*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ Request::is('faqs*') || Request::is('faqCategories*') ? 'active' : '' }}"> @if($icons)
                <i class="nav-icon fa fa-support"></i>@endif
            <p>{{trans('lang.faq_plural')}} <i class="right fa fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            @can('faqCategories.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('faqCategories*') ? 'active' : '' }}" href="{!! route('faqCategories.index') !!}">@if($icons)
                            <i class="nav-icon fa fa-folder"></i>@endif<p>{{trans('lang.faq_category_plural')}}</p></a>
                </li>
            @endcan

            @can('faqs.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('faqs*') ? 'active' : '' }}" href="{!! route('faqs.index') !!}">@if($icons)
                            <i class="nav-icon fa fa-question-circle"></i>@endif
                        <p>{{trans('lang.faq_plural')}}</p></a>
                </li>
            @endcan
        </ul>
    </li>
@endcan

@can('medias')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('medias*') ? 'active' : '' }}" href="{!! url('medias') !!}">@if($icons)<i class="nav-icon fa fa-picture-o"></i>@endif
            <p>{{trans('lang.media_plural')}}</p></a>
    </li>
@endcan

@can('payments.index')
    <li class="nav-item has-treeview {{ Request::is('market_transactions*') || Request::is('driversPayouts*') || Request::is('marketsPayouts*') || Request::is('driver_transactions*') || Request::is('driver-payout-requests*') || Request::is('market-payout-requests*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ Request::is('market_transactions*') || Request::is('driversPayouts*') || Request::is('marketsPayouts*') || Request::is('driver_transactions*') || Request::is('driver-payout-requests*') || Request::is('market-payout-requests*') ? 'active' : '' }}"> @if($icons)
                <i class="nav-icon fa fa-credit-card"></i>@endif
            <p>{{trans('lang.payment_plural')}}<i class="right fa fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">

{{--            @can('payments.index')--}}
{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link {{ Request::is('payments*') ? 'active' : '' }}" href="{!! route('payments.index') !!}">@if($icons)--}}
{{--                            <i class="nav-icon fa fa-money"></i>@endif<p>{{trans('lang.payment_plural')}}</p></a>--}}
{{--                </li>--}}
{{--            @endcan--}}

{{--            @can('earnings.index')--}}
{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link {{ Request::is('earnings*') ? 'active' : '' }}" href="{!! route('earnings.index') !!}">@if($icons)<i class="nav-icon fa fa-money"></i>@endif<p>{{trans('lang.earning_plural')}}  </p></a>--}}
{{--                </li>--}}
{{--            @endcan--}}

            @can('market_transactions.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('market_transactions*') ? 'active' : '' }}" href="{!! route('market_transactions.index') !!}">@if($icons)<i class="nav-icon fa fa-dollar"></i>@endif<p>{{trans('lang.market_transactions_plural')}}</p></a>
                </li>
            @endcan

            @can('marketsPayouts.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('marketsPayouts*') ? 'active' : '' }}" href="{!! route('marketsPayouts.index') !!}">
                        @if($icons)<i class="nav-icon fa fa-dollar"></i>@endif<p>Vendor Payouts</p></a>
                </li>
            @endcan

            @if(request()->user()->hasRole('admin'))
                @can('market-payout-requests.index')
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('market-payout-requests*') ? 'active' : '' }}" href="{!! route('market-payout-requests.index') !!}">
                            @if($icons)<i class="nav-icon fa fa-dollar"></i>@endif<p>Vendor Payout Requests</p>
                        </a>
                    </li>
                @endcan
            @endif


            @can('driver_transactions.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('driver_transactions*') ? 'active' : '' }}" href="{!! route('driver_transactions.index') !!}">@if($icons)<i class="nav-icon fa fa-dollar"></i>@endif<p>{{trans('lang.driver_transactions_plural')}}</p></a>
                </li>
            @endcan

            @can('driversPayouts.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('driversPayouts*') ? 'active' : '' }}" href="{!! route('driversPayouts.index') !!}">
                        @if($icons)<i class="nav-icon fa fa-dollar"></i>@endif<p>{{trans('lang.drivers_payout_plural')}}</p></a>
                </li>
            @endcan

            @can('driver-payout-requests.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('driver-payout-requests*') ? 'active' : '' }}" href="{!! route('driver-payout-requests.index') !!}">@if($icons)<i class="nav-icon fa fa-dollar"></i>@endif<p>{{trans('lang.driver_payout_requests_plural')}}</p></a>
                </li>
            @endcan

        </ul>
    </li>
@endcan

@can('pick-up-vehicles.index')

<li class="nav-item has-treeview {{ Request::is('pick-up-vehicles*') || Request::is('pickup-delivery-order-requests*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('pick-up-vehicles*') || Request::is('pickup-delivery-order-requests*') ? 'active' : '' }}"> @if($icons)
            <i class="nav-icon fa fa-shopping-basket"></i>@endif
        <p>{{trans('lang.pickup_and_delivery')}} <i class="right fa fa-angle-left"></i></p>
    </a>
    <ul class="nav nav-treeview">
        @can('pick-up-vehicles.index')
            <li class="nav-item">
                <a class="nav-link {{ Request::is('pick-up-vehicles*') ? 'active' : '' }}" href="{!! route('pick-up-vehicles.index') !!}">@if($icons)
                        <i class="nav-icon fa fa-tasks"></i>@endif<p>{{trans('lang.pickup_vehicle_plural')}}</p></a>
            </li>
        @endcan
        @can('pickup-delivery-order-requests.index')
            <li class="nav-item">
                <a class="nav-link {{ Request::is('pickup-delivery-order-requests*') ? 'active' : '' }}" href="{!! route('pickup-delivery-order-requests.index') !!}">@if($icons)
                        <i class="nav-icon fa fa-tasks"></i>@endif<p>{{trans('lang.pickup_delivery_order_requests_plural')}}</p></a>
            </li>
        @endcan
    </ul>
</li>

@endcan

@can('users.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('users*') ? 'active' : '' }}" href="{!! route('users.index') !!}">@if($icons)
                <i class="nav-icon fa fa-users"></i>@endif
            <p>{{trans('lang.user_plural')}}</p>
        </a>
    </li>
@endcan

@can('terms.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('terms*') ? 'active' : '' }}"
           href="{!! route('terms.index') !!}">@if($icons)<i class="nav-icon fa fa-lock"></i>@endif<p>Terms and Conditions</p></a>
    </li>
@endcan

@can('vendor-locations.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('vendor-locations*') ? 'active' : '' }}" href="{!! route('vendor-locations.index') !!}">@if($icons)
                <i class="nav-icon fa fa-tasks"></i>@endif
            <p>Vendor Locations</p>
        </a>
    </li>
@endcan

@can('driver-locations.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('driver-locations*') ? 'active' : '' }}" href="{!! route('driver-locations.index') !!}">@if($icons)
                <i class="nav-icon fa fa-tasks"></i>@endif
            <p>Driver Locations</p>
        </a>
    </li>
@endcan

@can('slides.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('slides*') ? 'active' : '' }}"
           href="{!! route('slides.index') !!}">@if($icons)<i class="nav-icon fa fa-magic"></i>@endif<p>{{trans('lang.slide_plural')}} </p></a>
    </li>
@endcan



{{--@can('app-settings')--}}
{{--<li class="nav-item has-treeview {{ Request::is('settings/mobile*') || Request::is('slides*') ? 'menu-open' : '' }}">--}}
{{--    <a href="#" class="nav-link {{ Request::is('settings/mobile*') || Request::is('slides*') ? 'active' : '' }}">--}}
{{--        @if($icons)<i class="nav-icon fa fa-mobile"></i>@endif--}}
{{--        <p>--}}
{{--            {{trans('lang.mobile_menu')}}--}}
{{--            <i class="right fa fa-angle-left"></i>--}}
{{--        </p></a>--}}
{{--    <ul class="nav nav-treeview">--}}
{{--        <li class="nav-item">--}}
{{--            <a href="{!! url('settings/mobile/globals') !!}" class="nav-link {{  Request::is('settings/mobile/globals*') ? 'active' : '' }}">--}}
{{--                @if($icons)<i class="nav-icon fa fa-cog"></i> @endif <p>{{trans('lang.app_setting_globals')}}  </p>--}}
{{--            </a>--}}
{{--        </li>--}}

{{--        <li class="nav-item">--}}
{{--            <a href="{!! url('settings/mobile/colors') !!}" class="nav-link {{  Request::is('settings/mobile/colors*') ? 'active' : '' }}">--}}
{{--                @if($icons)<i class="nav-icon fa fa-pencil"></i> @endif <p>{{trans('lang.mobile_colors')}}  </p>--}}
{{--            </a>--}}
{{--        </li>--}}

{{--        <li class="nav-item">--}}
{{--            <a href="{!! url('settings/mobile/home') !!}" class="nav-link {{  Request::is('settings/mobile/home*') ? 'active' : '' }}">--}}
{{--                @if($icons)<i class="nav-icon fa fa-home"></i> @endif <p>{{trans('lang.mobile_home')}}--}}
{{--                    </p>--}}
{{--            </a>--}}
{{--        </li>--}}

{{--        @can('slides.index')--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link {{ Request::is('slides*') ? 'active' : '' }}" href="{!! route('slides.index') !!}">@if($icons)<i class="nav-icon fa fa-magic"></i>@endif<p>{{trans('lang.slide_plural')}} </p></a>--}}
{{--            </li>--}}
{{--        @endcan--}}
{{--    </ul>--}}

{{--</li>--}}
{{--    <li class="nav-item has-treeview {{--}}
{{--    (Request::is('settings*') ||--}}
{{--     Request::is('users*')) && !Request::is('settings/mobile*')--}}
{{--        ? 'menu-open' : '' }}">--}}
{{--        <a href="#" class="nav-link {{--}}
{{--        (Request::is('settings*') ||--}}
{{--         Request::is('users*')) && !Request::is('settings/mobile*')--}}
{{--          ? 'active' : '' }}"> @if($icons)<i class="nav-icon fa fa-cogs"></i>@endif--}}
{{--            <p>{{trans('lang.app_setting')}} <i class="right fa fa-angle-left"></i>--}}
{{--            </p>--}}
{{--        </a>--}}
{{--        <ul class="nav nav-treeview">--}}
{{--            <li class="nav-item">--}}
{{--                <a href="{!! url('settings/app/globals') !!}" class="nav-link {{  Request::is('settings/app/globals*') ? 'active' : '' }}">--}}
{{--                    @if($icons)<i class="nav-icon fa fa-cog"></i> @endif <p>{{trans('lang.app_setting_globals')}}</p>--}}
{{--                </a>--}}
{{--            </li>--}}

{{--            @can('users.index')--}}
{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link {{ Request::is('users*') ? 'active' : '' }}" href="{!! route('users.index') !!}">@if($icons)--}}
{{--                            <i class="nav-icon fa fa-users"></i>@endif--}}
{{--                        <p>{{trans('lang.user_plural')}}</p></a>--}}
{{--                </li>--}}
{{--            @endcan--}}

{{--            <li class="nav-item has-treeview {{ Request::is('settings/permissions*') || Request::is('settings/roles*') ? 'menu-open' : '' }}">--}}
{{--                <a href="#" class="nav-link {{ Request::is('settings/permissions*') || Request::is('settings/roles*') ? 'active' : '' }}">--}}
{{--                    @if($icons)<i class="nav-icon fa fa-user-secret"></i>@endif--}}
{{--                    <p>--}}
{{--                        {{trans('lang.permission_menu')}}--}}
{{--                        <i class="right fa fa-angle-left"></i>--}}
{{--                    </p></a>--}}
{{--                <ul class="nav nav-treeview">--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link {{ Request::is('settings/permissions') ? 'active' : '' }}" href="{!! route('permissions.index') !!}">--}}
{{--                            @if($icons)<i class="nav-icon fa fa-circle-o"></i>@endif--}}
{{--                            <p>{{trans('lang.permission_table')}}</p>--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link {{ Request::is('settings/permissions/create') ? 'active' : '' }}" href="{!! route('permissions.create') !!}">--}}
{{--                            @if($icons)<i class="nav-icon fa fa-circle-o"></i>@endif--}}
{{--                            <p>{{trans('lang.permission_create')}}</p>--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link {{ Request::is('settings/roles') ? 'active' : '' }}" href="{!! route('roles.index') !!}">--}}
{{--                            @if($icons)<i class="nav-icon fa fa-circle-o"></i>@endif--}}
{{--                            <p>{{trans('lang.role_table')}}</p>--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link {{ Request::is('settings/roles/create') ? 'active' : '' }}" href="{!! route('roles.create') !!}">--}}
{{--                            @if($icons)<i class="nav-icon fa fa-circle-o"></i>@endif--}}
{{--                            <p>{{trans('lang.role_create')}}</p>--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                </ul>--}}

{{--            </li>--}}

{{--            <li class="nav-item">--}}
{{--                <a class="nav-link {{ Request::is('settings/customFields*') ? 'active' : '' }}" href="{!! route('customFields.index') !!}">@if($icons)--}}
{{--                        <i class="nav-icon fa fa-list"></i>@endif<p>{{trans('lang.custom_field_plural')}}</p></a>--}}
{{--            </li>--}}


{{--            <li class="nav-item">--}}
{{--                <a href="{!! url('settings/app/localisation') !!}" class="nav-link {{  Request::is('settings/app/localisation*') ? 'active' : '' }}">--}}
{{--                    @if($icons)<i class="nav-icon fa fa-language"></i> @endif <p>{{trans('lang.app_setting_localisation')}}</p></a>--}}
{{--            </li>--}}
{{--            <li class="nav-item">--}}
{{--                <a href="{!! url('settings/translation/en') !!}" class="nav-link {{ Request::is('settings/translation*') ? 'active' : '' }}">--}}
{{--                    @if($icons) <i class="nav-icon fa fa-language"></i> @endif <p>{{trans('lang.app_setting_translation')}}</p></a>--}}
{{--            </li>--}}
{{--            @can('currencies.index')--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link {{ Request::is('settings/currencies*') ? 'active' : '' }}" href="{!! route('currencies.index') !!}">@if($icons)<i class="nav-icon fa fa-dollar"></i>@endif<p>{{trans('lang.currency_plural')}}</p></a>--}}
{{--            </li>--}}
{{--            @endcan--}}

{{--            <li class="nav-item">--}}
{{--                <a href="{!! url('settings/payment/payment') !!}" class="nav-link {{  Request::is('settings/payment*') ? 'active' : '' }}">--}}
{{--                    @if($icons)<i class="nav-icon fa fa-credit-card"></i> @endif <p>{{trans('lang.app_setting_payment')}}</p>--}}
{{--                </a>--}}
{{--            </li>--}}

{{--            <li class="nav-item">--}}
{{--                <a href="{!! url('settings/app/social') !!}" class="nav-link {{  Request::is('settings/app/social*') ? 'active' : '' }}">--}}
{{--                    @if($icons)<i class="nav-icon fa fa-globe"></i> @endif <p>{{trans('lang.app_setting_social')}}</p>--}}
{{--                </a>--}}
{{--            </li>--}}


{{--            <li class="nav-item">--}}
{{--                <a href="{!! url('settings/app/notifications') !!}" class="nav-link {{  Request::is('settings/app/notifications*') ? 'active' : '' }}">--}}
{{--                    @if($icons)<i class="nav-icon fa fa-bell"></i> @endif <p>{{trans('lang.app_setting_notifications')}}</p>--}}
{{--                </a>--}}
{{--            </li>--}}

{{--            <li class="nav-item">--}}
{{--                <a href="{!! url('settings/mail/smtp') !!}" class="nav-link {{ Request::is('settings/mail*') ? 'active' : '' }}">--}}
{{--                    @if($icons)<i class="nav-icon fa fa-envelope"></i> @endif <p>{{trans('lang.app_setting_mail')}}</p>--}}
{{--                </a>--}}
{{--            </li>--}}

{{--        </ul>--}}
{{--    </li>--}}
{{--@endcan--}}
