<?php

namespace App\Http\Controllers;

use App\DataTables\AreaDataTable;
use App\DataTables\AttributeDataTable;
use App\DataTables\CityDataTable;
use App\Http\Requests\CreateAreaRequest;
use App\Http\Requests\CreateAttributesRequest;
use App\Http\Requests\CreateCityRequest;
use App\Http\Requests\UpdateAreaRequest;
use App\Http\Requests\UpdateAttributeRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Models\Area;
use App\Models\Attribute;
use App\Repositories\AreaRepository;
use App\Repositories\AttributesRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CityRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\FieldRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laracasts\Flash\Flash;
use Prettus\Validator\Exceptions\ValidatorException;

class AreaController extends Controller
{
    /** @var  AreaRepository */
    private $areaRepository;
    /** @var  CityRepository */
    private $cityRepository;

    public function __construct(AreaRepository $areaRepository, CityRepository $cityRepository)
    {
        parent::__construct();
        $this->areaRepository = $areaRepository;
        $this->cityRepository = $cityRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(AreaDataTable $areaDataTable)
    {
        return $areaDataTable->render('areas.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $city = $this->cityRepository->pluck('name','id');

        return view('areas.create')->with('city',$city);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateAreaRequest $request)
    {
        $input = $request->all();
        try {
            $area = $this->areaRepository->create($input);

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully',['operator' => __('lang.area')]));

        return redirect(route('areas.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $area = $this->areaRepository->findWithoutFail($id);
        $city = $this->cityRepository->pluck('name', 'id');

        if (empty($area)) {
            Flash::error(__('lang.not_found',['operator' => __('lang.area')]));

            return redirect(route('areas.index'));
        }

        return view('areas.edit')->with('city', $city)->with('area',$area)->with('city',$city);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, UpdateAreaRequest $request)
    {

        $area = $this->areaRepository->findWithoutFail($id);

        if (empty($area)) {
            Flash::error('Area not found');
            return redirect(route('areas.index'));
        }
        $input = $request->all();
        try {
            $area = $this->areaRepository->update($input, $id);

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully',['operator' => __('lang.area')]));

        return redirect(route('areas.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $area = $this->areaRepository->findWithoutFail($id);

        if (empty($area)) {
            Flash::error('Area not found');

            return redirect(route('areas.index'));
        }

        $this->areaRepository->delete($id);

        Flash::success(__('lang.deleted_successfully',['operator' => __('lang.area')]));

        return redirect(route('areas.index'));
    }

    public function cityCircle(Request $request, $cityId)
    {
        $areas = Area::where('city_id', $cityId)->get();

        return response()->json($areas, 200);
    }

}
