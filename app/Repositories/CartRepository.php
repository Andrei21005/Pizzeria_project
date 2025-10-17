<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

// Класс CartRepository, реализующий интерфейс CartRepositoryInterface
class CartRepository implements CartRepositoryInterface
{
    /**
     * Сохраняет корзину пользователя в базу данных.
     *
     * @param int $userId Идентификатор пользователя
     * @param array<int, array{id: int, quantity: int, price: float}> $cart
     * @return bool Успешность операции (true или false)
     */
    public function saveCart(int $userId, array $cart): bool
    {
        return DB::table('user_cart')->updateOrInsert(
            ['user_id' => $userId], // Условие для вставки или обновления записи
            ['items' => json_encode($cart), 'updated_at' => now()] // Данные для сохранения/обновления
        );
    }

    /**
     * Загружает корзину пользователя из базы данных.
     *
     * @param int $userId Идентификатор пользователя
     * @return ?string JSON-строка с данными корзины или null, если записи не найдено
     */
    public function loadCart(int $userId): ?string
    {
        return DB::table('user_cart')->where('user_id', $userId)->value('items'); // Возвращает поле 'items' или null
    }

    /**
     * Удаляет корзину пользователя из базы данных.
     *
     * @param int $userId Идентификатор пользователя
     * @return bool Успешность операции (true, если корзина удалена, false в противном случае)
     */
    public function deleteCart(int $userId): bool
    {
        return DB::table('user_cart')->where('user_id', $userId)->delete() > 0; // Проверяет, было ли удалено больше 0 строк
    }
}
