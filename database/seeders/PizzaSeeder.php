<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// Сидер для заполнения базы данных пиццами
class PizzaSeeder extends Seeder
{
    /**
     * Метод запуска сидера для создания пицц и их размеров.
     *
     * @return void
     */
    public function run()
    {
        // Чтение содержимого JSON-файла с данными о пиццах
        $json = file_get_contents(database_path('data/pizzas.json')); // Указание пути к файлу
        $pizzas = json_decode($json, true); // Преобразование JSON в ассоциативный массив
    
        // Итерация по каждому элементу массива пицц
        foreach ($pizzas as $pizzaData) {
            // Создание записи пиццы в базе данных
            $pizza = \App\Models\Pizza::create([
                'name' => $pizzaData['name'],
                'image_url' => $pizzaData['image_url'], 
                'ingredients' => $pizzaData['ingredients'], 
                'proteins' => $pizzaData['proteins'], 
                'fats' => $pizzaData['fats'], 
                'carbohydrates' => $pizzaData['carbohydrates'], 
            ]);
    
            // Добавление размеров для созданной пиццы
            foreach ($pizzaData['sizes'] as $sizeData) {
                $pizza->sizes()->create($sizeData); // Создание связанных записей размеров
            }
        }
    }
}
