<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use App\Traits\GeneralResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\EmailRequest;
use App\Notifications\EmailNotification;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    use GeneralResponse;

    public function __construct()
    {
        // $this->middleware('jwtAuth')->except('forgetPassword');
    }

    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users']
        ]);
        if ($validator->fails()) {
            return $this->returnError($validator->messages(),422);
        }

        $user = User::where([
            'email' => $request->email,
            'provider' => 'null',
        ])->first();

        $user->notify(new EmailNotification('use the below code for Reset Password process:', 'Reset Password'));

        if (!$token = JWTAuth::fromUser($user)) {
            return $this->returnError('Unauthorized',422);
        }
        $user->access_token = $token;
        return $this->returnData('Authenticated User' , $user);
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => ['required', 'max:6'],
            'email' => ['required', 'email'],
        ]);
        if ($validator->fails()) {
            return $this->returnError($validator->messages(),422);
        }

        $otp = new Otp();
        $otp_res =  $otp->validate($request->email, $request->otp);

        if (!$otp_res->status) {
            return $this->returnError($otp_res->message,422);
        }
        return $this->returnSuccessMessage('email is verified');
    }
    public function resetPassword(EmailRequest $request)
    {
        $user = User::where([
            'email' => $request->email,
            'provider' => "null",
        ])->first();

        if (!$user) {
            return $this->returnError('email in not correct or is not registered before',400);
        }
        $user->update([
            'password' => Hash::make($request->password),
        ]);


        return $this->returnSuccessMessage('Password is updated');
    }
}
