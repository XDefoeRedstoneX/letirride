<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject('Reset your '.config('app.name').' password')
            ->greeting('Hi '.($notifiable->name ?: 'there').',')
            ->line('We received a request to reset the password for your account. Click the button below to choose a new password.')
            ->action('Reset Password', $url)
            ->line('This link will expire in '.$expire.' minutes.')
            ->line('If you did not request a password reset, no action is needed — your password will stay the same.');
    }
}
