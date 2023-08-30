<?php

namespace App\Http\Controllers\API;


use App\Models\Option;
use App\Repositories\AttributesRepository;
use App\Repositories\OptionRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Illuminate\Support\Facades\Response;
use Prettus\Repository\Exceptions\RepositoryException;
use Flash;

/**
 * Class OptionController
 * @package App\Http\Controllers\API
 */

class AttributeAPIController extends Controller
{
    /** @var  AttributesRepository */
    private $attributesRepository;

    public function __construct(AttributesRepository $attributeRepo)
    {
        $this->attributesRepository = $attributeRepo;
    }

    /**
     * Display a listing of the Option.
     * GET|HEAD /options
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $this->attributesRepository->pushCriteria(new RequestCriteria($request));
            $this->attributesRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $attributes = $this->attributesRepository->all();

        return $this->sendResponse($attributes->toArray(), 'Attributes retrieved successfully');
    }

    /**
     * Display the specified Option.
     * GET|HEAD /options/{id}
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        /** @var Option $option */
        if (!empty($this->attributesRepository)) {
            $attribute = $this->attributesRepository->findWithoutFail($id);
        }

        if (empty($attribute)) {
            return $this->sendError('Attribute not found');
        }

        return $this->sendResponse($attribute->toArray(), 'Attribute retrieved successfully');
    }
}
