<?php

namespace App\Http\Controllers;

use App\Services\NotificationDestinationResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request, NotificationDestinationResolver $resolver): JsonResponse
    {
        $notifications = $request->user()->notifications()->latest()->limit(20)->get()->map(fn ($notification) => [
            'id' => $notification->id,
            'title' => data_get($notification->data, 'title', 'Puihaha update'),
            'message' => data_get($notification->data, 'message', ''),
            'url' => $resolver->resolve($request->user(), data_get($notification->data, 'url')),
            'read' => $notification->read_at !== null,
            'created_at' => $notification->created_at?->diffForHumans(),
        ]);

        return response()->json([
            'unread' => $request->user()->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, string $notification): JsonResponse
    {
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->each->markAsRead();

        return response()->json(['ok' => true]);
    }
}
