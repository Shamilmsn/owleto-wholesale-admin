<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\SubscriptionPackageRepository;
use Illuminate\Http\Request;

class PackageAPIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /** @var  SubscriptionPackageRepository */
    private $packageRepository;

    public function __construct(SubscriptionPackageRepository $packageRepository)
    {
        $this->SubscriptionPackageRepository = $packageRepository;
    }
    public function index(Request $request)
    {
        $packages = $this->SubscriptionPackageRepository
            ->with('market')
            ->with('product')
            ->with('user')
            ->with('package_days')
            ->with('package_delivery_times')
            ->orderBy('id','desc');

        if ($request->market_id) {
            $packages = $this->SubscriptionPackageRepository->where('market_id', $request->market_id);
        }

        $packages = $packages->get();

        return $this->sendResponse($packages->toArray(), 'Packages retrieved successfully');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $packageRow = $this->SubscriptionPackageRepository->findWithoutFail($id);

        $package = $this->SubscriptionPackageRepository->with('market')->with('product')
            ->with('user')->with('package_days')->with('package_delivery_times')->where('id',$id)->first();

        return $this->sendResponse($package->toArray(), 'Package retrieved successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
