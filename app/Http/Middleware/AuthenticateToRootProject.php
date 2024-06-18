<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthenticateToRootProject
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = Http::withHeaders([
            'Authorization' => $request->request->get('access'),
        ])->post(env('ROOT_PROJECT').'/api-qr/authenticate', [
            'individual_id' => $request->request->get('individual_id')
        ]);
        if ($response->successful()) {
            return $next($request);
        }else{
            return response('Unauthorized', 401);
        }
    }
}
