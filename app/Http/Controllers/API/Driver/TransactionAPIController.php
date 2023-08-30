<?php
/**
 * File name: UserAPIController.php
 * Last modified: 2020.10.29 at 17:03:54
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API\Driver;

use App\Criteria\DriverTransactions\DriverTransactionOfUsers;
use App\Events\UserRoleChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\DriverTransaction;
use App\Models\User;
use App\Repositories\CustomFieldRepository;
use App\Repositories\DriverTransactionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class TransactionAPIController extends Controller
{
    /**
     * @var DriverTransactionRepository
     */
    private $driverTransactionRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DriverTransactionRepository $driverTransactionRepository)
    {
        $this->driverTransactionRepository = $driverTransactionRepository;
    }

    public function index(Request $request)
    {
        try{
            $this->driverTransactionRepository->pushCriteria(new RequestCriteria($request));
            $this->driverTransactionRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->driverTransactionRepository->pushCriteria(new DriverTransactionOfUsers($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $attributes = $this->driverTransactionRepository->latest()->get();

        return $this->sendResponse($attributes->toArray(), 'Transaction histories retrieved successfully');
    }

    public function getDriverBalance(Request $request)
    {
       $driverBalance =  DriverTransaction::where('user_id',$request->user_id)->latest()->first();
       $driverBalance = $driverBalance ? $driverBalance->balance : 0;

        return $this->sendResponse($driverBalance, 'Driver Balance retrieved successfully');
    }

}
