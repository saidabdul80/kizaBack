<?php

namespace App\Http\Middleware;



class CheckScopes
{
    /**
     * Specify the scopes for the middleware.
     *
     * @param  array|string  $scopes
     * @return string
     */
    public static function using(...$scopes)
    {
        if (is_array($scopes[0])) {
            return static::class.':'.implode(',', $scopes[0]);
        }

        return static::class.':'.implode(',', $scopes);
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$scopes
     * @return \Illuminate\Http\Response
     *
     * @throws \Laravel\Passport\Exceptions\AuthenticationException|\Laravel\Passport\Exceptions\MissingScopeException
     */
    public function handle($request, $next, ...$scopes)
    {
        if (! $request->user() || ! $request->user()->token()) {
            abort(401,"Unauthorized");
        }

        foreach ($scopes as $scope) {
            if (! $request->user()->tokenCan($scope)) {
                abort(401,"Unauthorized");
            }
        }

        return $next($request);
    }
}
