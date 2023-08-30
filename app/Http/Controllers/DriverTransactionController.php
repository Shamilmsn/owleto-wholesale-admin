<?php

namespace App\Http\Controllers;

use App\DataTables\DriverTransactionDataTable;
use App\Models\DriverTransaction;
use Illuminate\Http\Request;

class DriverTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(DriverTransactionDataTable $driverTransactionDataTable)
    {
        return $driverTransactionDataTable->render('driver_transactions.index');
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
     * @param  \App\Models\DriverTransaction  $driverTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(DriverTransaction $driverTransaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DriverTransaction  $driverTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(DriverTransaction $driverTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DriverTransaction  $driverTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DriverTransaction $driverTransaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DriverTransaction  $driverTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(DriverTransaction $driverTransaction)
    {
        //
    }
}
