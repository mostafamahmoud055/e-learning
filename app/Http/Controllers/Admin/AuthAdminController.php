<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use App\Traits\GeneralResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\SignInRequest;

class AuthAdminController extends Controller
{
    use GeneralResponse;
    public $admin;
    public $username;

    public function __construct()
    {
        $this->middleware('auth')->except(['login', 'register']);
    }
    public function login(SignInRequest $request)
    {
        $this->admin = Admin::Where('email', $request->username)
            ->orWhere('phone_number', $request->username)
            ->first();
        if (!$this->admin) {
            return $this->returnError("Sorry, your email/phone or password is incorrect .Please try again.",400);
        }
        $login_type = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';
        $credentials = [$login_type => $request->username, 'password' => $request->password];

        if ($this->admin && Hash::check($request->password, $this->admin->password)) {
            if (!$token = Auth::guard('admin')->attempt($credentials)) {
                return $this->returnError('Unauthorized',401);
            }
            Auth::guard('admin')->user()->token = $token;

            return $this->returnData('Authenticated User' , auth("admin")->user());
        }
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return $this->returnSuccessMessage("you are logged out successfully");
    }
}
