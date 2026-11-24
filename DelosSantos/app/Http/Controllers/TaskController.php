<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskPortalNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $tasks = $this->memberTasks();

        return view('tasks', $this->taskViewData($tasks));
    }

    public function myTasks(): View
    {
        $tasks = $this->memberTasks();

        return view('tasks.my-tasks', $this->taskViewData($tasks));
    }

    public function complete(Request $request, Task $task): RedirectResponse|JsonResponse
    {
        abort_unless($task->member_id === auth()->user()->member_id, 404);
        abort_if($task->archived_at !== null, 404);

        $wasCompleted = (bool) $task->completed;
        $nowCompleted = ! $wasCompleted;

        DB::transaction(function () use ($task, $nowCompleted): void {
            $task->update([
                'completed' => $nowCompleted,
                'completed_at' => $nowCompleted ? now() : null,
                'status' => $nowCompleted ? Task::STATUS_COMPLETED : Task::STATUS_ASSIGNED,
            ]);

            AuditLog::record(
                $nowCompleted ? 'task.completed' : 'task.reopened',
                ['task_id' => $task->id, 'member_id' => $task->member_id],
                $task,
                auth()->user(),
            );

            DB::afterCommit(function () use ($task, $nowCompleted): void {
                $admins = User::where('role', User::ROLE_ADMIN)
                    ->where('status', User::STATUS_ACTIVE)
                    ->get();

                Notification::sendNow($admins, new TaskPortalNotification(
                    $nowCompleted ? 'Task completed' : 'Task reopened',
                    auth()->user()->member?->full_name.' '.($nowCompleted ? 'completed' : 'reopened').' '.$task->task_title.'.',
                    route('admin.tasks.index'),
                ));
            });
        });

        if ($request->expectsJson()) {
            $tasks = $this->memberTasks();
            $updatedTask = $tasks->firstWhere('id', $task->id);
            $summary = $this->taskSummary($tasks);

            return response()->json([
                'task' => [
                    'id' => $updatedTask->id,
                    'completed' => (bool) $updatedTask->completed,
                    'status_key' => $updatedTask->effectiveStatus(),
                    'status_label' => ucfirst(str_replace('_', ' ', $updatedTask->effectiveStatus())),
                    'filter_status' => $this->filterStatus($updatedTask),
                ],
                'summary' => $summary,
                'completed_by_day' => $this->completedByDay($tasks),
            ]);
        }

        return back()->with('success', $nowCompleted ? 'Task marked complete.' : 'Task moved back to active.');
    }

    private function memberTasks(): Collection
    {
        return Task::with(['extensionRequests' => fn ($query) => $query->latest()])
            ->where('member_id', auth()->user()->member_id)
            ->whereNull('archived_at')
            ->latest()
            ->get();
    }

    private function taskViewData(Collection $tasks): array
    {
        $member = auth()->user()->member;
        $hasSavedLocation = is_numeric($member?->latitude) && is_numeric($member?->longitude);

        return [
            'tasks' => $tasks,
            'priorityTasks' => $this->priorityTasks($tasks),
            'taskByDay' => $this->taskByDay($tasks),
            'completedByDay' => $this->completedByDay($tasks),
            'taskSummary' => $this->taskSummary($tasks),
            'weatherLocation' => [
                'latitude' => $hasSavedLocation ? $member->latitude : config('membership.weather.default_latitude'),
                'longitude' => $hasSavedLocation ? $member->longitude : config('membership.weather.default_longitude'),
                'label' => $hasSavedLocation ? 'Your saved location' : 'Puihaha weather',
                'uses_default' => ! $hasSavedLocation,
            ],
        ];
    }

    private function priorityTasks(Collection $tasks): Collection
    {
        return $tasks
            ->filter(fn (Task $task) => ! $task->completed)
            ->sortBy(function (Task $task): string {
                $rank = match ($task->priority ?? Task::PRIORITY_MEDIUM) {
                    Task::PRIORITY_HIGH => '1',
                    Task::PRIORITY_LOW => '3',
                    default => '2',
                };

                return $rank.'|'.($task->deadline?->format('YmdHis') ?? '99999999999999').'|'.$task->id;
            })
            ->take(5)
            ->values();
    }

    private function taskByDay(Collection $tasks): Collection
    {
        return collect(range(6, 0))->mapWithKeys(function (int $daysAgo) use ($tasks): array {
            $date = now()->subDays($daysAgo);

            return [$date->format('M d') => $tasks->filter(fn (Task $task) => $task->created_at?->isSameDay($date))->count()];
        });
    }

    private function completedByDay(Collection $tasks): Collection
    {
        return collect(range(13, 0))->mapWithKeys(function (int $daysAgo) use ($tasks): array {
            $date = now()->subDays($daysAgo);

            return [$date->format('M d') => $tasks->filter(fn (Task $task) => $task->completed_at?->isSameDay($date))->count()];
        });
    }

    private function taskSummary(Collection $tasks): array
    {
        $completed = $tasks->where('completed', true)->count();
        $assigned = $tasks->count();

        return [
            'assigned' => $assigned,
            'todo' => $assigned - $completed,
            'completed' => $completed,
            'overdue' => $tasks->filter(fn (Task $task) => ! $task->completed && $task->effectiveStatus() === 'overdue')->count(),
            'completion_percent' => $assigned ? (int) round(($completed / $assigned) * 100) : 0,
        ];
    }

    private function filterStatus(Task $task): string
    {
        if ($task->completed) {
            return 'completed';
        }

        if ($task->extensionRequests->contains(fn ($request) => $request->status === 'pending')) {
            return 'extension_requested';
        }

        return $task->effectiveStatus() === 'overdue' ? 'overdue' : 'todo';
    }
}
