<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (empty($roles)) {
            return $next($request);
        }

        foreach ($roles as $role) {
            $gateName = "access-{$role}";
            if (Gate::allows($gateName)) {
                return $next($request);
            }
        }

        return redirect('/login')->with('error', "Silakan Login");
    }
}