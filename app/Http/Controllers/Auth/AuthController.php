<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\GeneralResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\EmailNotification;
use App\Http\Requests\Auth\SignInRequest;
use App\Http\Requests\Auth\SignUpRequest;

class AuthController extends Controller
{
    use GeneralResponse;
    public $user;
    public $username;

    public function __construct()
    {
        $this->middleware('jwtAuth')->except(['login', 'register']);
    }
    public function register(SignUpRequest $request)
    {
        $this->user = User::Where('email', $request->email)
            ->where('provider', "null")
            ->first();

        if ($this->user) {
            return $this->returnError("The email has already been taken.", 400);
        }
        $username = strstr($request->email, '@', true);

        $this->user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'role' => $request->role,
            'code' => '1',
            'username' => $username,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
        ]);
        if ($this->user) {
            $this->user->notify(new EmailNotification('use the below code for Email Verification process:', 'Email Verification'));
            return $this->returnData("$request->role registered successfully", $this->user);
        }
    }

    public function login(SignInRequest $request)
    {

        $this->user = User::Where('email', $request->username)
            ->where('provider', "null")
            ->orWhere('phone_number', $request->username)
            ->first();

        if (!$this->user) {
            return $this->returnError("Sorry, your email/phone or password is incorrect .Please try again.", 400);
        }
        if ($this->user && Hash::check($request->password, $this->user->password)) {
            if (!$token = JWTAuth::fromUser($this->user)) {
                return $this->returnError('Unauthorized', 422);
            }
            $this->user->update([
                'online' => 1
            ]);

            $this->user->access_token = $token;

            return $this->returnData('Authenticated User', $this->user);
        } else {
            return $this->returnError("Sorry, your email/phone or password is incorrect .Please try again.", 400);
        }
    }

    public function logout()
    {
        $user = User::find(Auth::id());
        $user->update([
            'online' => 0
        ]);

        auth()->logout();
        return $this->returnSuccessMessage("you are logged out successfully");
    }
}
