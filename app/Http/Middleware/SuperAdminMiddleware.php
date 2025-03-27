<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->is_super_admin) {
            return $next($request);
        }
        // Redirige ou affiche un message d'erreur si l'utilisateur n'est pas super admin.
        return redirect()->route('dashboard')->with('error', 'Accès refusé. Vous n\'êtes pas autorisé à effectuer cette action.');
    }
}
