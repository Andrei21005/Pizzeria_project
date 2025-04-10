<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);
        
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }
        
        $request->session()->regenerate();
    
        $user = Auth::user();
    
        // Загрузка корзины из базы данных
        $savedCart = DB::table('user_cart')->where('user_id', $user->id)->value('items');
    
        if ($savedCart) {
            Cookie::queue('cart', $savedCart, 60); // Возвращаем корзину в cookies
        }
    
        return redirect()->intended('/dashboard');
    }
    

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Извлечение данных корзины из cookies
        $cart = json_decode($request->cookie('cart'), true) ?? [];

        // Сохранение корзины в базу данных
        if ($user && !empty($cart)) {
            DB::table('user_cart')->updateOrInsert(
                ['user_id' => $user->id], // Уникальный пользователь
                ['items' => json_encode($cart), 'updated_at' => now()]
            );
        }

        // Удаление данных корзины из cookies
        Cookie::queue(Cookie::forget('cart'));
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
