<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;

class PrivacyPolicyController extends Controller
{
    public function index(UserDataTable $userDataTable)
    {
       return view('privacy-policies.index');
    }
}
