<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    /** @var OtpService $otpService */
    var $otpService;

    /**
     * OtpController constructor.
     * @param OtpService $otpService
     */
    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function send(Request $request)
    {
        $response = $this->otpService->send($request);

        if($response == 'invalid email'){

            return $this->sendError('Please enter a valid email', 401);
        }

        return $this->sendResponse($response, 'OTP successfully send', );
    }

    public function verify(Request $request)
    {
        $response = $this->otpService->verify($request);

        if (!$response) {
            return $this->sendError('Invalid Code', 403);
        }

        return $this->sendResponse(null, 'OTP successfully verified' );
    }
}
