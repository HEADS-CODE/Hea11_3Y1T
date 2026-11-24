<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $invitationUrl)
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
            ->subject('Set up your Puihaha member account')
            ->greeting('Welcome to Puihaha.')
            ->line('An administrator created a pending membership record for you.')
            ->line('Use the secure one-time invitation below to set your password and confirm your email address.')
            ->action('Set password and confirm email', $this->invitationUrl)
            ->line('This invitation expires in 24 hours and can only be used once.')
            ->line('After accepting, complete the membership payment so an administrator can approve your portal access.');
    }
}
