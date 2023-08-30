<?php

namespace App\Repositories;

use App\Models\DriverPayoutRequest;

class DriverPayoutRequestRepository extends BaseRepository
{
    public function model()
    {
        return DriverPayoutRequest::class;
    }
}