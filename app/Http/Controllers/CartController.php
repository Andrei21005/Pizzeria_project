<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\PizzaRepositoryInterface;
use App\Repositories\CartRepositoryInterface;
use App\Services\MailService;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;

class CartController extends Controller
{
    protected PizzaRepositoryInterface $pizzaRepository;
    protected CartRepositoryInterface $cartRepository;
    protected MailService $mailService;

    public function __construct(PizzaRepositoryInterface $pizzaRepository, CartRepositoryInterface $cartRepository, MailService $mailService)
    {
        $this->pizzaRepository = $pizzaRepository;
        $this->cartRepository = $cartRepository;
        $this->mailService = $mailService;
    }

    /**
     * Отображение содержимого корзины
     */
    public function index(Request $request): View|string
    {
        $cartCookie = $request->cookie('cart');
        $cart = is_string($cartCookie) ? json_decode($cartCookie, true) ?? [] : [];
        
        $pizzaIds = array_column($cart, 'id'); // Извлечение ID пицц из корзины
        $pizzas = $this->pizzaRepository->getByIDs($pizzaIds); // Получение данных о пиццах по ID

        $cartDetails = [];
        $totalPrice = 0;

        foreach ($cart as $item) {
            $pizza = $pizzas->firstWhere('id', $item['id']);
            if ($pizza) {
                $sizes = $pizza->sizes;
                $size = $sizes->firstWhere('size_name', $item['size']);
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

        if ($request->ajax()) {
            return view('partials.cart', compact('cartDetails', 'totalPrice'))->render();
        }
        return view('cart', compact('cartDetails', 'totalPrice'));
    }

    /**
     * Добавление пиццы в корзину
     */
    public function add(Request $request): JsonResponse
    {
        $id = $request->input('id'); // ID пиццы
        $size = $request->input('size'); // Размер пиццы

        $cartCookie = $request->cookie('cart');
        if (is_string($cartCookie)) {
            $cart = json_decode($cartCookie, true) ?? [];
        } else {
            $cart = [];
        }

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

        $response = response()->json(['success' => true, 'cart' => $cart]);
        return $response->cookie('cart', json_encode($cart), 60);
    }

    /**
     * Удаление пиццы из корзины
     */
    public function remove(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $size = $request->input('size');

        $cartCookie = $request->cookie('cart');
        if (is_string($cartCookie)) {
            $cart = json_decode($cartCookie, true) ?? [];
        } else {
            $cart = [];
        }
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
    public function saveCart(Request $request): JsonResponse
    {
       /** @var User|null $user */
        $user = Auth::user();
        $cart = $request->input('cart');

        if ($user instanceof User && !empty($cart)) {
            $this->cartRepository->saveCart($user->id, $cart);
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error']);
    }

    /**
     * Загрузка сохраненной корзины из базы данных
     */
    public function loadCart(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user instanceof User) {
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
    public function confirmDelivery(Request $request): JsonResponse
    {

        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Пользователь не авторизован'], 401);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255'
        ]);

        $lastSent = Cache::get('email_code_last_sent_' . Auth::id());
        if ($lastSent && Carbon::parse($lastSent)->diffInMinutes(Carbon::now()) < 5) {
            return response()->json([
                'error' => 'Повторная отправка кода возможна только через 5 минут'
            ], 429);
        }

        $confirmationCode = random_int(1000, 9999); 
        $expiresAt = now()->addMinutes(5); 

        try {
            $this->mailService->sendConfirmationMail(Auth::user()->email, $confirmationCode);

            Cache::put('email_code_' . Auth::id(), $confirmationCode, $expiresAt); 
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
    public function verifyCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|numeric'
        ]);

        $user_id = Auth::id();
        $savedCode = Cache::get('email_code_' . $user_id);
        $code = $request->input('code');
        
        if(!is_int($user_id)){
            return response()->json([
                'success' => false,
                'message' => '!'
            ]);
        }

        if (!$savedCode) {
            return response()->json([
                'success' => false,
                'message' => 'Код подтверждения не найден или истек срок действия'
            ]);
        }
    
        if ($code != $savedCode) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный код подтверждения'
            ]);
        }     

        Cache::forget('email_code_' . Auth::id());
        $this->cartRepository->deleteCart(Auth::id());
        return response()->json([
            'success' => true,
            'message' => 'Код подтвержден успешно!'
        ]);
    }
}