<?php

namespace App\Http\Controllers;

use App\DataTables\ComplaintDataTable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    public function index(ComplaintDataTable $dataTable)
    {
        return $dataTable->render('complaints.index');
    }
}
