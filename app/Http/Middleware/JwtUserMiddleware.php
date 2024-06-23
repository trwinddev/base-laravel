<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponseTrait;
use Closure;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class JwtUserMiddleware extends BaseMiddleware
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if (!Auth::guard('user')->check()) {
                return $this->responseError(Response::HTTP_UNAUTHORIZED, 'Token invalid');
            }
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return $this->responseError(Response::HTTP_UNAUTHORIZED, 'Token invalid');
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return $this->responseError(Response::HTTP_UNAUTHORIZED, 'Token expired');
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenBlacklistedException) {
                return $this->responseError(Response::HTTP_UNAUTHORIZED, 'Token is Blacklisted');
            } else {
                return $this->responseError(Response::HTTP_UNAUTHORIZED, 'Authorization Token not found');
            }
        }
        return $next($request);
    }
}
