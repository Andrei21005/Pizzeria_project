<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

// Класс для отправки email с кодом подтверждения
class ConfirmationMail extends Mailable
{
    // Публичное свойство для хранения кода подтверждения
    public $confirmationCode;

    /**
     * Конструктор класса.
     *
     * @param int|string $confirmationCode Код подтверждения, который будет отправлен в письме
     */
    public function __construct($confirmationCode)
    {
        $this->confirmationCode = $confirmationCode; // Сохранение кода подтверждения
    }

    /**
     * Построение сообщения email.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Ваш код подтверждения заказа') // Установка темы письма
                    ->view('emails.confirmation') // Указание представления для письма
                    ->with(['code' => $this->confirmationCode]); // Передача данных в представление
    }
}