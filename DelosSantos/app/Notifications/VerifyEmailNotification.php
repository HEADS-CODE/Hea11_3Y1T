<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $token)
    {
        $this->afterCommit = true;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirm your Puihaha email address')
            ->greeting('Welcome to Puihaha.')
            ->line('Please confirm your email address to continue your membership application.')
            ->action('Confirm email', route('verification.verify', ['user' => $notifiable->id, 'token' => $this->token]))
            ->line('This link expires in 24 hours.');
    }
}
