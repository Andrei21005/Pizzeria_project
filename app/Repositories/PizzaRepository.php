<?php

namespace App\Repositories;

use App\Models\Pizza;

// Класс PizzaRepository, реализующий интерфейс PizzaRepositoryInterface
class PizzaRepository implements PizzaRepositoryInterface
{
    /**
     * Получение всех пицц с их доступными размерами.
     *
     * @return \Illuminate\Database\Eloquent\Collection Список всех пицц с размерами
     */
    public function getAll()
    {
        return Pizza::with('sizes')->get(); // Загружает все пиццы с их размерами через связь
    }

    /**
     * Поиск пиццы по идентификатору.
     *
     * @param int $id Идентификатор пиццы
     * @return \App\Models\Pizza Модель пиццы
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Если пицца с данным ID не найдена
     */
    public function findByID($id)
    {
        return Pizza::with('sizes')->findOrFail($id); // Находит пиццу с заданным ID или выбрасывает исключение
    }
    
    /**
     * Получение пицц по массиву идентификаторов.
     *
     * @param array $ids Массив идентификаторов пицц
     * @return \Illuminate\Database\Eloquent\Collection Список пицц с размерами
     */
    public function getByIDs(array $ids)
    {
        return Pizza::whereIn('id', $ids)->with('sizes')->get(); // Загружает пиццы, чьи ID содержатся в массиве
    }
}
