<?php

namespace App\Http\Middleware;

use Closure;

class EnforceJsonContent
{
    private const HEADER_NAME = 'Accept';
    private const HEADER_VALUE = 'application/json';
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $contentType = $request->header(self::HEADER_NAME);

        if (is_null($contentType) || $contentType != self::HEADER_VALUE) {
            $request->headers->set(self::HEADER_NAME, self::HEADER_VALUE);
        }
        
        return $next($request);
    }
}
