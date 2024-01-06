<?php

namespace App\Http\Controllers\SocialAuth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\GeneralResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\Auth\SocialRequest;

class SocialAuthController extends Controller
{
    use GeneralResponse;
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        $provider_user = Socialite::driver($provider)->stateless()->user();
        $user = User::where([
            'email' => $provider_user->email,
            'provider' => $provider,
        ])->first();

        if (!$user) {
            $code = $provider == 'google' ? '2' : '3';
            $username = strstr($provider_user->email, '@', true);
            $user = User::create([
                'first_name' => explode(" ", $provider_user->name)[0],
                'last_name' => explode(" ", $provider_user->name)[1],
                'email' => $provider_user->email,
                'email_verified_at' => now(),
                'online' => 1,
                'provider' => $provider,
                'provider_id' =>  $provider_user->id,
                'image'=>$provider_user->getAvatar(),
                'code' =>  $code,
                'username' =>  $username,
                'password' => Hash::make($provider_user->id . 'p@$$w0rd'),
                'provider_token' => $provider_user->token,
            ]);
        }
        if ($user) {
            // return $provider_user;
            return redirect()->to("http://localhost:5173/#/socialLogin/success/?username=$user->username&code=$user->code");
        }

    }

    public function socialAuth(SocialRequest $request)
    {
        $user = User::where([
            'username' => $request->username,
            'code' => $request->code,
        ])->first();

        // $user->update([
        //     'role' => $request->role
        // ]);

        if (!$token = JWTAuth::fromUser($user)) {
            return $this->returnError('Unauthorized',422);
        }
        $user->markEmailAsVerified();
        $user->access_token = $token;

        return $this->returnData('Authenticated User' , $user);
    }
}
