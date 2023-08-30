<?php

namespace App\Http\Controllers;

use App\DataTables\AreaDataTable;
use App\DataTables\TermsAndConditionsDataTable;
use App\Models\Term;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Validation\Rule;

class TermController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(TermsAndConditionsDataTable $dataTable)
    {
        return $dataTable->render('terms.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $types = Term::$types;

        return view('terms.create', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|unique:terms',
            'terms_and_conditions' => 'required'
        ]);

        $term = new Term();
        $term->type = $request->type;
        $term->terms_and_conditions = $request->terms_and_conditions;
        $term->save();

        Flash::success(__('lang.saved_successfully', ['operator' => 'Terms and conditions']));

        return redirect(route('terms.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Term  $term
     * @return \Illuminate\Http\Response
     */
    public function show(Term $term)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Term  $term
     * @return \Illuminate\Http\Response
     */
    public function edit(Term $term)
    {
        $types = Term::$types;
        return view('terms.edit', compact('term', 'types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Term  $term
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Term $term)
    {
        $request->validate([
            'type' => [
                'required',
                Rule::unique('terms')->ignore($term->id),
            ],
            'terms_and_conditions' => 'required'
        ]);

        $term->type = $request->type;
        $term->terms_and_conditions = $request->terms_and_conditions;
        $term->save();

        Flash::success(__('lang.saved_successfully', ['operator' => 'Terms and conditions']));

        return redirect(route('terms.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Term  $term
     * @return \Illuminate\Http\Response
     */
    public function destroy(Term $term)
    {
        $term->delete();

        Flash::success(__('lang.deleted_successfully',['operator' => 'Terms and conditions']));

        return redirect(route('terms.index'));
    }
}
