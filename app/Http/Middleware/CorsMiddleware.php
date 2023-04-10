<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $headers = [
            //'Access-Control-Allow-Origin'      => 'http://172.16.64.6:8000',
//		'Access-Control-Allow-Origin'      => 'http://rtgk_test_front',
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Authorization, Origin, Accept, Content-Type', 'Access-Control-Allow-Headers', 'Authorization', 'X-Requested-With', 'x-total-count', 'X-Header-Two'
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}
