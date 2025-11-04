<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $action  The action to check (edit or delete)
     * @param  string  $module  The module name
     */
    public function handle(Request $request, Closure $next, string $action, string $module): Response
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized access.');
        }

        $user = Auth::user();

        // Admin has all permissions
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if user has permission for the action
        if (!$user->hasPermission($module, $action)) {
            abort(403, 'You do not have permission to ' . $action . ' in this module.');
        }

        return $next($request);
    }
}
