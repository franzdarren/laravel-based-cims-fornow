<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Usage in routes: ->middleware('role:Administrator,Supervisor')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->isActive()) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'This account has been deactivated. Please contact your Administrator.',
            ]);
        }

        if (! empty($roles) && ! in_array($user->role->role_name, $roles, true)) {
            abort(403, 'You do not have access to this page.');
        }

        return $next($request);
    }
}
