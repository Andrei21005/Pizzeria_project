<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmationMail;

// Класс MailService для работы с электронной почтой
class MailService
{
    /**
     * Отправляет письмо с кодом подтверждения пользователю.
     *
     * @param string $email Адрес электронной почты пользователя
     * @param int $confirmationCode Код подтверждения, который необходимо отправить
     * @return void
     */
    public function sendConfirmationMail($email, $confirmationCode)
    {
        // Использование фасада Mail для отправки письма на указанный адрес
        Mail::to($email)->send(new ConfirmationMail($confirmationCode)); // Создание и отправка письма с кодом подтверждения
    }
}
