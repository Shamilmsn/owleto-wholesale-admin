<?php

namespace App\Http\Controllers\API;


use App\Models\AttributeOption;
use App\Models\OptionGroup;
use App\Repositories\AttributeOptionRepository;
use App\Repositories\OptionGroupRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Illuminate\Support\Facades\Response;
use Prettus\Repository\Exceptions\RepositoryException;
use Flash;

/**
 * Class OptionGroupController
 * @package App\Http\Controllers\API
 */

class AttributeOptionAPIController extends Controller
{
    /** @var  AttributeOptionRepository */
    private $attributeOptionRepository;

    public function __construct(AttributeOptionRepository $attributeoptionRepo)
    {
        $this->attributeOptionRepository = $attributeoptionRepo;
    }

    /**
     * Display a listing of the OptionGroup.
     * GET|HEAD /optionGroups
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $this->attributeOptionRepository->pushCriteria(new RequestCriteria($request));
            $this->attributeOptionRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $attributeOption = $this->attributeOptionRepository->all();

        return $this->sendResponse($attributeOption->toArray(), 'Attribute Option retrieved successfully');
    }

    /**
     * Display the specified OptionGroup.
     * GET|HEAD /optionGroups/{id}
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        /** @var AttributeOption $attributeOption */
        if (!empty($this->attributeOptionRepository)) {
            $attributeOption = $this->attributeOptionRepository->findWithoutFail($id);
        }

        if (empty($attributeOption)) {
            return $this->sendError('Attribute Option not found');
        }

        return $this->sendResponse($attributeOption->toArray(), 'Attribute Option retrieved successfully');
    }
}
