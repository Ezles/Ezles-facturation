<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        // Si l'utilisateur est déjà connecté, rediriger vers le dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Régénérer la session pour éviter les attaques de fixation de session
        $request->session()->regenerate();

        // Forcer la persistance de la session (se souvenir de l'utilisateur)
        // La durée par défaut est de 5 ans (comme défini dans config/session.php)
        $rememberTokenCookieName = Auth::getRecallerName();
        Cookie::queue(
            $rememberTokenCookieName,
            Cookie::get($rememberTokenCookieName),
            config('session.lifetime'),
            null,
            null,
            config('session.secure'),
            true, // HttpOnly
            false, // Raw
            'strict' // SameSite
        );

        // Rediriger vers le dashboard (page de facturation)
        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
