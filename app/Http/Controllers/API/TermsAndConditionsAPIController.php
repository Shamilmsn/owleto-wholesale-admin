<?php

namespace App\Http\Controllers\API;

use App\Models\Term;
use App\Repositories\CustomFieldRepository;
use App\Repositories\ProductRepository;
use App\Repositories\TermsRepository;
use App\Repositories\UploadRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Flash;
class TermsAndConditionsAPIController extends Controller
{
    /**
     * @var TermsRepository
     */

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => [
                'required',
                'in:USER,MARKET,VENDOR',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try{
            $terms = Term::query()
             ->where('type', $request->type)
             ->first();
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($terms, 'Terms retrieved successfully');
    }
}
