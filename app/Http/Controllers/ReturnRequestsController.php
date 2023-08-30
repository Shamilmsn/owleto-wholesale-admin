<?php

namespace App\Http\Controllers;

use App\Criteria\Users\ClientsCriteria;
use App\DataTables\ReturnRequestDatatable;
use App\DataTables\TodayPackageOrderDatatable;
use App\Models\Driver;
use App\Models\DriversCurrentLocation;
use App\Models\DriverTransaction;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PackageOrder;
use App\Models\User;
use App\Notifications\DriverAssignedNotification;
use App\Notifications\DriverAssignedNotificationToUser;
use App\Repositories\CustomFieldRepository;
use App\Repositories\MarketRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderStatusRepository;
use App\Repositories\PackageOrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Kreait\Firebase\Contract\Database;
use Laracasts\Flash\Flash;

class ReturnRequestsController extends Controller
{
    public function index(ReturnRequestDatatable $datatable)
    {
        return $datatable->render('return-requests.index');
    }
}
