<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PizzaController;

// Основной маршрут для главной страницы
Route::get('/', function () {
    return view('welcome'); 
})->name('welcome'); 

// Маршрут для отображения каталога пицц
Route::get('/catalog', [PizzaController::class, 'catalog'])->name('pizzas.catalog'); 

// Маршрут для отображения страницы конкретной пиццы
Route::get('/products/{id}', [PizzaController::class, 'show'])->name('pizzas.show'); 

// Маршрут для отображения корзины
Route::get('/cart', [CartController::class, 'index'])->name('cart.index'); 

// Маршрут для добавления пиццы в корзину
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add'); 

// Маршрут для удаления пиццы из корзины
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove'); 
// Маршрут для сохранения корзины пользователя
Route::post('/save-cart', [CartController::class, 'saveCart']); 

// Маршрут для проверки кода подтверждения (доступен только для авторизованных пользователей)
Route::post('/verify-code', [CartController::class, 'verifyCode'])->middleware('auth'); 

// Маршрут для панели управления (только для авторизованных и подтвержденных пользователей)
Route::get('/dashboard', function () {
    return view('dashboard'); 
})->middleware(['auth', 'verified'])->name('dashboard'); 

// Маршрут для отправки данных о доставке
Route::post('/confirm-delivery', [CartController::class, 'confirmDelivery']); 

// Группировка маршрутов, доступных только для авторизованных пользователей
Route::middleware('auth')->group(function () {
    // Маршрут для редактирования профиля
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit'); 
    // Маршрут для обновления профиля
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update'); 

    // Маршрут для удаления профиля
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); 
});

// Подключение файла с маршрутами для аутентификации
require __DIR__.'/auth.php';
