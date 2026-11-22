<?php

namespace App\Notifications;

use App\Models\MemberRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailChangeVerificationNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly MemberRequest $memberRequest, private readonly string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify your new Puihaha email address')
            ->greeting('Please verify your new email address.')
            ->line('This address will be used only if your profile-change request is approved.')
            ->action('Verify new email', route('member.requests.verify-email', ['memberRequest' => $this->memberRequest->id, 'token' => $this->token]))
            ->line('This link expires in 24 hours.');
    }
}
