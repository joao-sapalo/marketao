<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtpCode extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly string $type = 'registration'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->type) {
            'registration' => 'Código de Verificação - MarketAO ERP',
            'password_reset' => 'Recuperação de Senha - MarketAO ERP',
            default => 'Código de Verificação - MarketAO ERP',
        };

        $message = match ($this->type) {
            'registration' => 'Use o código abaixo para verificar o seu email e activar a sua conta.',
            'password_reset' => 'Use o código abaixo para redefinir a sua senha.',
            default => 'Use o código abaixo para continuar.',
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Olá!')
            ->line($message)
            ->line("**{$this->code}**")
            ->line('Este código expira em 10 minutos.')
            ->line('Se não solicitou esta acção, ignore este email.')
            ->salutation('Equipa MarketAO ERP');
    }
}
