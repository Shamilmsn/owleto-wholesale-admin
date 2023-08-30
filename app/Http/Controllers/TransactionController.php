<?php

namespace App\Http\Controllers;

use App\DataTables\MarketTransactionDataTable;
use App\Models\MarketTransaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{

    /**
     * Display a listing of the OrderStatus.
     *
     * @param MarketTransactionDataTable $marketTransactionTable
     * @return Response
     */
    public function index(MarketTransactionDataTable $marketTransactionTable)
    {
        return $marketTransactionTable->render('market_transactions.index');
    }


    public function show(MarketTransaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MarketTransaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(MarketTransaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MarketTransaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MarketTransaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MarketTransaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(MarketTransaction $transaction)
    {
        //
    }
}
