<?php

namespace App\Repositories;

use App\Models\Pizza;
use Illuminate\Database\Eloquent\Collection;

// Класс PizzaRepository, реализующий интерфейс PizzaRepositoryInterface
class PizzaRepository implements PizzaRepositoryInterface
{
    /**
     * Получение всех пицц с их доступными размерами.
     *
     * @return Collection<int, Pizza>
     */
    public function getAll(): Collection
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
    public function findByID($id): Pizza
    {
        return Pizza::with('sizes')->findOrFail($id); // Находит пиццу с заданным ID или выбрасывает исключение
    }

    /**
     * Получение пицц по массиву идентификаторов.
     *
     * @param int[] $ids Массив идентификаторов пицц
     * @return Collection<int, Pizza>
     */
    public function getByIDs(array $ids): Collection
    {
        return Pizza::query()->whereIn('id', $ids)->with('sizes')->get();
    }
}
