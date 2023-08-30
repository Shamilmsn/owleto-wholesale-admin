<?php

namespace App\Http\Controllers\API;


use App\Criteria\Carts\CartsOfUsersCriteria;
use App\Http\Requests\CreateCartRequest;
use App\Http\Requests\CreateFavoriteRequest;
use App\Models\Cart;
use App\Models\CartAddon;
use App\Models\Complaint;
use App\Repositories\CartRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Illuminate\Support\Facades\Response;
use Prettus\Repository\Exceptions\RepositoryException;
use Flash;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class CartController
 * @package App\Http\Controllers\API
 */
class ComplaintAPIController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'phone' => ['required'],
            'email' => ['required'],
            'complaint' => ['required'],
        ]);

        $complaint = new Complaint();
        $complaint->name = $request->name;
        $complaint->phone = $request->phone;
        $complaint->email = $request->email;
        $complaint->complaint = $request->complaint;
        $complaint->role = Auth::user()->latestRole()->name;
        $complaint->user_id = Auth::id();
        $complaint->save();

        return $this->sendResponse($complaint, __('Complaints added successfully'));
    }

}
