<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MutateVariable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $query = $request->query();

        // Remove null or empty parameters
        foreach ($query as $key => $value) {
            if (is_null($value) || $value === '' || $value === 'null') {
                $request->query->remove($key);
            }
        }

        // Check and handle the 'paginate' parameter
        if ($request->has('paginate') && $request->paginate > 0) {
            config(['default.pagination_length' => (int) $request->paginate]);
            $request->query->remove('paginate');
        }
        
       /*  if ($request->query() != $query) {           
            return redirect()->to($request->url() . '?' . http_build_query($request->query()));
        } */
        
        return $next($request);
    }
}
