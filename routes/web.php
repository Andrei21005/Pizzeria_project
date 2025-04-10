<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PizzaController;

// Основной маршрут для главной страницы
Route::get('/', function () {
    return view('welcome'); // Возврат представления "welcome"
})->name('welcome'); // Присвоение имени маршруту

// Маршрут для отображения каталога пицц
Route::get('/catalog', [PizzaController::class, 'catalog'])->name('pizzas.catalog'); // Привязка метода catalog контроллера PizzaController

// Маршрут для отображения страницы конкретной пиццы
Route::get('/products/{id}', [PizzaController::class, 'show'])->name('pizzas.show'); // Привязка метода show контроллера PizzaController с параметром id

// Маршрут для отображения корзины
Route::get('/cart', [CartController::class, 'index'])->name('cart.index'); // Привязка метода index контроллера CartController

// Маршрут для добавления пиццы в корзину
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add'); // Привязка метода add контроллера CartController

// Маршрут для удаления пиццы из корзины
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove'); // Привязка метода remove контроллера CartController

// Маршрут для сохранения корзины пользователя
Route::post('/save-cart', [CartController::class, 'saveCart']); // Привязка метода saveCart контроллера CartController

// Маршрут для проверки кода подтверждения (доступен только для авторизованных пользователей)
Route::post('/verify-code', [CartController::class, 'verifyCode'])->middleware('auth'); // Применение middleware auth

// Маршрут для панели управления (только для авторизованных и подтвержденных пользователей)
Route::get('/dashboard', function () {
    return view('dashboard'); // Возврат представления "dashboard"
})->middleware(['auth', 'verified'])->name('dashboard'); // Применение middleware auth и verified

// Маршрут для отправки данных о доставке
Route::post('/confirm-delivery', [CartController::class, 'confirmDelivery']); // Привязка метода confirmDelivery контроллера CartController

// Группировка маршрутов, доступных только для авторизованных пользователей
Route::middleware('auth')->group(function () {
    // Маршрут для редактирования профиля
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit'); // Привязка метода edit контроллера ProfileController
    
    // Маршрут для обновления профиля
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update'); // Привязка метода update контроллера ProfileController
    
    // Маршрут для удаления профиля
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); // Привязка метода destroy контроллера ProfileController
});

// Подключение файла с маршрутами для аутентификации
require __DIR__.'/auth.php';
