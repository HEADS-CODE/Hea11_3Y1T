<?php

namespace App\Notifications;

use App\Models\User;
use App\Services\NotificationDestinationResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskPortalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $title,
        private readonly string $message,
        private readonly string $url,
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $notifiable instanceof User
                ? app(NotificationDestinationResolver::class)->resolve($notifiable, $this->url)
                : null,
        ];
    }
}
