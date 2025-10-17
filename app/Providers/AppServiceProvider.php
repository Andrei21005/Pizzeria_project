<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Repositories\PizzaRepository;
use App\Repositories\CartRepository;
use App\Repositories\PizzaRepositoryInterface;
use App\Repositories\CartRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind(PizzaRepositoryInterface::class, PizzaRepository::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Для консольных команд не выполняем
        if ($this->app->runningInConsole()) {
            return;
        }

        // Инициализация гостевой сессии
        if (Auth::guest() && !session()->has('guest_id')) {
            session()->put([
                'guest_id' => 'guest_'.Str::random(20),
                'cart' => []
            ]);
        }
    }
}
