<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

// Класс для отправки email с кодом подтверждения
class ConfirmationMail extends Mailable
{
    // Публичное свойство для хранения кода подтверждения
    public int $confirmationCode;

    /**
     * Конструктор класса.
     *
     * @param int $confirmationCode Код подтверждения, который будет отправлен в письме
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
        return $this->subject('Ваш код подтверждения заказа') 
                    ->view('emails.confirmation') 
                    ->with(['code' => $this->confirmationCode]); 
    }
}
