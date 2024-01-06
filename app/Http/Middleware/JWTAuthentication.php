<?php

namespace App\Http\Middleware;

use App\Traits\GeneralResponse;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JWTAuthentication
{
    use GeneralResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            if ($e instanceof TokenExpiredException) {
                $token = JWTAuth::getToken();
                $newToken = JWTAuth::refresh($token);
                return $this->returnData('Token Expired', $newToken, 'new_token');
            } else if ($e instanceof TokenInvalidException) {
                return $this->returnError('Token Invalid',401);
            } else {
                return $this->returnError('Token Not Found',401);
            }
        }
        return $next($request);
    }
}
