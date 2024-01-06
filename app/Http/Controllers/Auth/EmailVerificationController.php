<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use App\Traits\GeneralResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmailRequest;
use App\Notifications\EmailNotification;

class EmailVerificationController extends Controller
{
    use GeneralResponse;
    public $otp;
    public function __construct()
    {
        $this->otp = new Otp;
    }
    public function emailVerification(EmailRequest $request)
    {
        $otp_res = $this->otp->validate($request->email, $request->otp);

        if (!$otp_res->status) {
            return $this->returnError($otp_res->message,422);
        }

        $user = User::Where('email', $request->email)->first();

        $user->markEmailAsVerified();

        return $this->returnSuccessMessage('Email is verified');
    }
    public function resendEmailVerification(EmailRequest $request)
    {

        $user = User::Where('email', $request->email)->first();
        if (!$user) {
            return $this->returnError('email in not correct or is not registered before',400);
        }
        $user->notify(new EmailNotification('use the below code for Email Verification process:','Email Verification'));

        return $this->returnSuccessMessage('verification code is sent');
    }
}
