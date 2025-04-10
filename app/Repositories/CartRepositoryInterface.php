<?php

namespace App\Repositories;

interface CartRepositoryInterface
{
    public function saveCart(int $userId, array $cart): bool;
    public function loadCart(int $userId): ?string;
    public function deleteCart(int $userId);
}