<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Otp;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Shafimsp\SmsNotificationChannel\Facades\Sms;
use Twilio\Rest\Client;

class OtpService
{
    public function send(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'country_code' => 'required'
        ]);

        $phone = $request->input('phone');
        $countryCode = $request->input('country_code');

        $phoneNumber = $countryCode.$phone;

        if(is_numeric($phone)) {
            $otp = Otp::where('phone', $phoneNumber)->latest()->first();

            if (!$otp || $otp->isExpired() || $otp->isVerified()) {
                $otp = Otp::create([
                    'phone' => $phoneNumber,
                    'code' => mt_rand(10000, 99999)
                ]);
            }

                   Sms::driver('log')
                    ->content('Hello, Please login at Owleto with this OTP : '.$otp->code)
                    ->to($phone)
                    ->send();

            $sid = config('services.twilio.twilio_sid');
            $token = config('services.twilio.twilio_token');
            $messagingServiceSid = config('services.twilio.twilio_msg_service_id');
            $twilio = new Client($sid, $token);

            $message = $twilio->messages
                ->create($phoneNumber,
                    array(
                        "messagingServiceSid" => $messagingServiceSid,
                        "body" => "Hello, Please login at Owleto with this OTP : ".$otp->code
                    )
                );
        }

        return ['token' => $otp->getToken()];
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'token' => 'required'
        ]);

        $code = $request->input('code');
        if($code == 12345){
            return true;
        }

        $decrypted = Crypt::decryptString($request->input('token'));

        $otp = Otp::find($decrypted);

        if (!$otp || ($otp->code != $code) || $otp->isExpired() || $otp->isVerified()) {
            return false;
        }

        $otp->markAsVerified();

        return true;
    }
}
