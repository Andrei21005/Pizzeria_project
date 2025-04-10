<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Pizza;
use App\Repositories\PizzaRepositoryInterface;

// Контроллер для работы с пиццами
class PizzaController extends Controller
{
    // Репозиторий для работы с данными пицц
    protected $pizzaRepository;

    // Конструктор, в котором происходит внедрение зависимости репозитория
    public function __construct(PizzaRepositoryInterface $pizzaRepository)
    {
        $this->pizzaRepository = $pizzaRepository;
    }

    /**
     * Метод для отображения каталога пицц
     */
    public function catalog()
    {
        $pizzas = $this->pizzaRepository->getAll(); // Получение списка всех пицц
        $user = Auth::user(); // Получение данных текущего авторизованного пользователя
        return view('catalog', compact('pizzas', 'user')); // Возврат вида каталога с передачей данных
    }

    /**
     * Метод для отображения конкретной пиццы по её ID
     * 
     * @param int $id Идентификатор пиццы
     */
    public function show($id)
    {
        $pizza = $this->pizzaRepository->findByID($id); // Поиск пиццы по её ID
        $user = Auth::user(); // Получение данных текущего авторизованного пользователя
        return view('products', compact('pizza', 'user')); // Возврат вида продукта с передачей данных
    }
}