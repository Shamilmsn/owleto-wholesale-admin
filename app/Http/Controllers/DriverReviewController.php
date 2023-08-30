<?php

namespace App\Http\Controllers;

use App\DataTables\DriverReviewDataTable;

class DriverReviewController extends Controller
{
    public function index(DriverReviewDataTable $dataTable)
    {
        return $dataTable->render('driver-reviews.index');
    }
}
