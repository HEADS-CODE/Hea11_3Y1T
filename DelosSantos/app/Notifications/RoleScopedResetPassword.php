<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RoleScopedResetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly string $role,
    ) {
        $this->afterCommit = true;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'role' => $this->role,
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $message = (new MailMessage)
            ->subject('Reset your Puihaha password')
            ->line('You are receiving this email because a password reset was requested for your Puihaha account.')
            ->action('Reset password', $url)
            ->line('This password reset link will expire in '.config('auth.passwords.'.($this->role === 'admin' ? 'admins' : 'members').'.expire').' minutes.')
            ->line('If you did not request a password reset, no further action is required.');

        if ($this->role === User::ROLE_MEMBER && $notifiable instanceof User && $notifiable->isAwaitingApproval()) {
            $message->line('After resetting your password, sign in using this member account to continue your membership payment. Administrator approval follows after payment.');
        }

        return $message;
    }
}
