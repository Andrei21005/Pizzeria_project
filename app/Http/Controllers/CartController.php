<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\PizzaRepositoryInterface;
use App\Repositories\CartRepositoryInterface;
use App\Services\MailService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Репозитории и сервисы, которые будут использоваться в контроллере
    protected $pizzaRepository;
    protected $cartRepository;
    protected $mailService;

    // Конструктор класса, принимающий зависимости через внедрение зависимостей
    public function __construct(PizzaRepositoryInterface $pizzaRepository, CartRepositoryInterface $cartRepository, MailService $mailService)
    {
        $this->pizzaRepository = $pizzaRepository;
        $this->cartRepository = $cartRepository;
        $this->mailService = $mailService;
    }

    /**
     * Отображение содержимого корзины
     */
    public function index(Request $request)
    {
        // Получение данных корзины из cookie и преобразование их в массив
        $cart = json_decode($request->cookie('cart'), true) ?? [];
        $pizzaIds = array_column($cart, 'id'); // Извлечение ID пицц из корзины
        $pizzas = $this->pizzaRepository->getByIDs($pizzaIds); // Получение данных о пиццах по ID

        $cartDetails = [];
        $totalPrice = 0;

        // Формирование массива деталей корзины и подсчет общей стоимости
        foreach ($cart as $item) {
            $pizza = $pizzas->firstWhere('id', $item['id']);
            if ($pizza) {
                $size = $pizza->sizes->firstWhere('size_name', $item['size']);
                if ($size) {
                    $cartDetails[] = [
                        'id' => $pizza->id,
                        'name' => $pizza->name,
                        'size' => $item['size'],
                        'price' => $size->price,
                        'quantity' => $item['quantity'],
                        'image_url' => $pizza->image_url,
                    ];
                    $totalPrice += $size->price * $item['quantity'];
                }
            }
        }

        // Если запрос AJAX, возвращается частичный шаблон, иначе общий
        if ($request->ajax()) {
            return view('partials.cart', compact('cartDetails', 'totalPrice'))->render();
        }
        return view('cart', compact('cartDetails', 'totalPrice'));
    }
    
    /**
     * Добавление пиццы в корзину
     */
    public function add(Request $request)
    {
        $id = $request->input('id'); // ID пиццы
        $size = $request->input('size'); // Размер пиццы

        $cart = json_decode($request->cookie('cart'), true) ?? [];

        $key = $id . ':' . $size; // Уникальный ключ на основе ID и размера
        if (isset($cart[$key])) {
            $cart[$key]['quantity']++; // Увеличение количества, если пицца уже есть
        } else {
            $cart[$key] = [
                'id' => $id,
                'size' => $size,
                'quantity' => 1,
            ];
        }

        // Возвращается обновленная корзина с установкой cookie
        $response = response()->json(['success' => true, 'cart' => $cart]);
        return $response->cookie('cart', json_encode($cart), 60); 
    }

    /**
     * Удаление пиццы из корзины
     */
    public function remove(Request $request)
    {
        $id = $request->input('id'); 
        $size = $request->input('size'); 

        $cart = json_decode($request->cookie('cart'), true) ?? [];
        $key = $id . ':' . $size;

        if (isset($cart[$key])) {
            $cart[$key]['quantity']--; // Уменьшение количества пиццы
            if ($cart[$key]['quantity'] <= 0) {
                unset($cart[$key]); // Удаление пиццы, если количество меньше или равно нулю
            }
        }

        // Обновление cookie с новой корзиной
        $response = response()->json(['success' => true, 'cart' => $cart]);
        return $response->cookie('cart', json_encode($cart), 60); 
    }

    /**
     * Сохранение текущей корзины в базе данных для авторизованного пользователя
     */
    public function saveCart(Request $request)
    {
        $user = Auth::user();
        $cart = $request->input('cart');

        if ($user && !empty($cart)) {
            $this->cartRepository->saveCart($user->id, $cart);
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error']);
    }

    /**
     * Загрузка сохраненной корзины из базы данных
     */
    public function loadCart(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $savedCart = $this->cartRepository->loadCart($user->id);

            if ($savedCart) {
                $response = response()->json(['status' => 'loaded']);
                return $response->cookie('cart', $savedCart, 60);
            }
        }

        return response()->json(['status' => 'no_cart']);
    }

    /**
     * Отправка кода подтверждения на email пользователя
     */
    public function confirmDelivery(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255'
        ]);
        
        // Проверка времени последней отправки кода
        $lastSent = Cache::get('email_code_last_sent_' . Auth::id());
        if ($lastSent && Carbon::parse($lastSent)->diffInMinutes(Carbon::now()) < 5) {
            return response()->json([
                'error' => 'Повторная отправка кода возможна только через 5 минут'
            ], 429);
        }
        
        $confirmationCode = random_int(1000, 9999); // Генерация случайного кода подтверждения
        $expiresAt = now()->addMinutes(5); // Установка срока действия кода

        try {
            // Отправка кода на email пользователя
            $this->mailService->sendConfirmationMail(Auth::user()->email, $confirmationCode);

            Cache::put('email_code_' . Auth::id(), $confirmationCode, $expiresAt); // Сохранение кода в кеш
            Cache::put('email_code_last_sent_' . Auth::id(), now(), now()->addMinutes(5));
            
            return response()->json([
                'message' => 'Код подтверждения отправлен на вашу почту!',
                'expires_at' => $expiresAt->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка при отправке кода подтверждения: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Проверка кода подтверждения
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric'
        ]);

        $savedCode = Cache::get('email_code_' . Auth::id());

        if (!$savedCode) {
            return response()->json([
                'success' => false,
                'message' => 'Код подтверждения не найден или истек срок действия'
            ]);
        }

        if ($request->code != $savedCode) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный код подтверждения'
            ]);
        }

        // Удаление кода из кеша и корзины
        Cache::forget('email_code_' . Auth::id());
        $this->cartRepository->deleteCart(Auth::id());
        return response()->json([
            'success' => true,
            'message' => 'Код подтвержден успешно!'
        ]);
    }
}