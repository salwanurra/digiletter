<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCampusIdentityVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'user' && ! $user->hasVerifiedCampusIdentity()) {
            return redirect()->route('campus-identity.show');
        }

        return $next($request);
    }
}
