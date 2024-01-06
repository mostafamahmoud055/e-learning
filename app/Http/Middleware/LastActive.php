<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Carbon;

class LastActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            User::where([
                'email' => $user->email,
                'provider' => 'null',
            ])->first();
            $user->forceFill([
                'last_active_at' => Carbon::now()->diffForHumans(),
            ])->save();
        }
        return $next($request);
    }
}
