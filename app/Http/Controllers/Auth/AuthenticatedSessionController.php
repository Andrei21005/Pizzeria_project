<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class AuthenticatedSessionController extends Controller
{
    /**
     * Отображение представленя регистрации.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
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

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (Auth::check() === false) {
            return back()->withErrors(['auth' => 'User not authenticated']);
        }
        // Загрузка корзины из базы данных
        $savedCart = DB::table('user_cart')->where('user_id', $user->id)->value('items');

        if ($savedCart) {
            Cookie::queue('cart', $savedCart, 60); // Возвращаем корзину в cookies
        }

        return redirect()->intended('/dashboard');
    }


    /**
     * Завершение сесии пользователя
     */
    public function destroy(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Извлечение данных корзины из cookies
        $cartRaw = $request->cookie('cart');
        $cart = is_string($cartRaw) ? json_decode($cartRaw, true) : [];

        // Сохранение корзины в базу данных
        if ($user && !empty($cart)) {
            DB::table('user_cart')->updateOrInsert(
                ['user_id' => $user->id], // Уникальный пользователь
                ['items' => json_encode($cart), 'updated_at' => now()]
            );
        }

        // Удаление данных корзины из cookies
        Cookie::queue(Cookie::forget('cart'));
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
