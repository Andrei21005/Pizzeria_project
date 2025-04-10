<?php

namespace App\Repositories;

interface PizzaRepositoryInterface 
{
    public function getAll();
    public function findByID($id);
    public function getByIDs(array $ids);
}