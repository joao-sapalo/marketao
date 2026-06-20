<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $code
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Recuperação de Senha - MarketAO ERP')
            ->greeting('Olá!')
            ->line('Recebemos um pedido de redefinição de senha para a sua conta.')
            ->line("Seu código de recuperação: **{$this->code}**")
            ->line('Este código expira em 10 minutos.')
            ->line('Se não solicitou a redefinição de senha, ignore este email.')
            ->salutation('Equipa MarketAO ERP');
    }
}
