<?php

namespace App\Repositories;

interface CartRepositoryInterface
{
    /**
     * @param array<int, array{id: int, quantity: int, price: float}> $cart
     */
    public function saveCart(int $userId, array $cart): bool;
    public function loadCart(int $userId): ?string;
    public function deleteCart(int $userId): bool;
}
