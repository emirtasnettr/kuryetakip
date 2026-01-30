<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Panel Authentication Controller
 * 
 * Web panel için giriş/çıkış işlemleri.
 */
class AuthController extends Controller
{
    /**
     * Giriş formu
     */
    public function showLoginForm()
    {
        return view('panel.auth.login');
    }

    /**
     * Giriş işlemi
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Giriş denemesi
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Girdiğiniz bilgiler hatalı.']);
        }

        $user = Auth::user();

        // Aktiflik kontrolü
        if (!$user->is_active) {
            Auth::logout();
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Hesabınız pasif durumda.']);
        }

        // Panel erişim yetkisi kontrolü
        if (!$user->canAccessPanel()) {
            Auth::logout();
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Panel erişim yetkiniz bulunmuyor.']);
        }

        // Session yenile
        $request->session()->regenerate();

        // Son giriş bilgilerini güncelle
        $user->updateLoginInfo($request->ip());

        return redirect()->intended(route('panel.dashboard'));
    }

    /**
     * Çıkış işlemi
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('panel.login');
    }
}
