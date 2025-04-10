<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pizza;
use App\Models\PizzaSize;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RefreshPizzasFromJson extends Command
{
    protected $signature = 'pizzas:refresh';
    protected $description = 'Очищает и перезаполняет таблицы пицц из JSON';

    public function handle()
    {
        // 1. Определяем тип базы данных
        $isPostgreSQL = config('database.default') === 'pgsql';

        // 2. Очистка таблиц с учетом типа БД
        if ($isPostgreSQL) {
            $this->clearPostgresTables();
        } else {
            $this->clearRegularTables();
        }

        // 3. Загрузка данных из JSON
        $this->loadDataFromJson();

        $this->info('Таблицы успешно обновлены!');
    }

    protected function clearPostgresTables()
    {
        // Для PostgreSQL используем TRUNCATE с CASCADE
        DB::statement('TRUNCATE TABLE pizzas, pizza_sizes RESTART IDENTITY CASCADE;');
    }

    protected function clearRegularTables()
    {
        // Оригинальная реализация для MySQL/SQLite
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PizzaSize::truncate();
        Pizza::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    protected function loadDataFromJson()
    {
        $jsonPath = database_path('data/pizzas.json');
        
        if (!File::exists($jsonPath)) {
            $this->error("Файл $jsonPath не найден!");
            return;
        }

        $data = json_decode(File::get($jsonPath), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Ошибка парсинга JSON: '.json_last_error_msg());
            return;
        }

        $bar = $this->output->createProgressBar(count($data));
        
        DB::transaction(function () use ($data, $bar) {
            foreach ($data as $pizzaData) {
                $pizza = Pizza::create([
                    'name' => $pizzaData['name'],
                    'image_url' => $pizzaData['image_url'],
                    'ingredients' => $pizzaData['ingredients'],
                    'proteins' => $pizzaData['proteins'] ?? null,
                    'fats' => $pizzaData['fats'] ?? null,
                    'carbohydrates' => $pizzaData['carbohydrates'] ?? null,
                ]);

                foreach ($pizzaData['sizes'] as $sizeData) {
                    $pizza->sizes()->create($sizeData);
                }
                
                $bar->advance();
            }
        });
        
        $bar->finish();
    }
}