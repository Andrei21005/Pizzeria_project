<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Pizza;

interface PizzaRepositoryInterface
{
    /**
     * Получение всех пицц.
     *
     * @return Collection<int, Pizza>
     */
    public function getAll(): Collection;

    /**
     * Поиск пиццы по идентификатору.
     *
     * @param int $id
     * @return Pizza
     */
    public function findByID(int $id): Pizza;

    /**
     * Получение списка пицц по массиву идентификаторов.
     *
     * @param array<int> $ids
     * @return Collection<int, Pizza>
     */
    public function getByIDs(array $ids): Collection;
}