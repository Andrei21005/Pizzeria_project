@if(count($cartDetails) > 0)
    <table class="table table-striped mt-4 text-center">
        <thead>
            <tr>
                <th>Товар</th>
                <th>Название</th>
                <th>Размер</th>
                <th>Количество</th>
                <th>Цена</th>
                <th>Сумма</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cartDetails as $item)
                <tr data-id="{{ $item['id'] }}" data-size="{{ $item['size'] }}">
                    <td class="align-middle ">
                        <div class="d-flex justify-content-center align-items-center">
                            <img src="{{ asset($item['image_url']) }}" alt="{{ $item['name'] }}" style="width: 50px; height: 50px;"> 
                        </div>
                    </td>    
                    <td class="align-middle">{{ $item['name'] }}</td>       
                    <td class="align-middle">{{ $item['size'] }}</td>
                    <td class="quantity align-middle">{{ $item['quantity'] }}</td>
                    <td class="align-middle">{{ $item['price'] }} руб.</td>
                    <td class="sum align-middle">{{ $item['quantity'] * $item['price'] }} руб.</td>
                    <td class="align-middle">
                        <button data-id="{{ $item['id'] }}" data-size="{{ $item['size'] }}" onclick="removeFromCart(event)" class="btn btn-danger btn-sm">-</button>
                        <button data-id="{{ $item['id'] }}" data-size="{{ $item['size'] }}" onclick="addToCart(event)" class="btn btn-success btn-sm">+</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <h3 class="cart-total mt-4">Общая стоимость: {{ $totalPrice }} руб.</h3>
    <button id="confirmButton" class="btn btn-primary">Заказать</button>

    <div class="modal fade" id="deliveryModal" tabindex="-1" aria-labelledby="deliveryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div id="step1" class="modal-step">
                    <div class="modal-header">
                        <h5 class="modal-title">Подтверждение доставки</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nameInput" class="form-label">Ваше имя</label>
                            <input type="text" class="form-control" id="nameInput" required>
                        </div>
                        <div class="mb-3">
                            <label for="addressInput" class="form-label">Адрес доставки</label>
                            <input type="text" class="form-control" id="addressInput" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="button" class="btn btn-primary" id="sendCodeButton">Отправить код</button>
                    </div>
                </div>
                <div id="step2" class="modal-step" style="display:none;">
                    <div class="modal-header">
                        <h5 class="modal-title">Подтвердите код</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Код подтверждения отправлен на вашу почту.</p>
                        <div class="mb-3">
                            <label for="confirmationCodeInput" class="form-label">Введите код</label>
                            <input type="text" class="form-control" id="confirmationCodeInput" placeholder="XXXX">
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-link p-0" id="resendCodeButton">Отправить код повторно</button>
                            <span id="resendTimer" class="text-muted ms-2" style="display:none;"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="backButton">Назад</button>
                        <button type="button" class="btn btn-primary" id="confirmCodeButton">Подтвердить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-warning mt-4">Ваша корзина пуста.</div>
@endif