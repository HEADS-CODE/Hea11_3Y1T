<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskExtensionDecisionRequest;
use App\Http\Requests\TaskExtensionRequestStoreRequest;
use App\Models\AuditLog;
use App\Models\Task;
use App\Models\TaskExtensionRequest;
use App\Models\User;
use App\Notifications\TaskPortalNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TaskExtensionRequestController extends Controller
{
    public function store(TaskExtensionRequestStoreRequest $request, Task $task): RedirectResponse
    {
        abort_unless((int) $task->member_id === (int) $request->user()->member_id, 404);

        if ($task->archived_at !== null || $task->completed) {
            return back()->with('error', 'A completed or archived task cannot receive an extension request.');
        }

        if ($task->extensionRequests()->where('status', TaskExtensionRequest::STATUS_PENDING)->exists()) {
            return back()->with('error', 'This task already has an extension request awaiting review.');
        }

        $data = $request->validated();
        $extension = DB::transaction(function () use ($data, $task, $request): TaskExtensionRequest {
            $extension = TaskExtensionRequest::create([
                ...$data,
                'task_id' => $task->id,
                'member_id' => $request->user()->member_id,
            ]);

            AuditLog::record('task.extension_requested', [
                'task_id' => $task->id,
                'member_id' => $request->user()->member_id,
                'requested_deadline' => null,
            ], $extension, $request->user());

            DB::afterCommit(function () use ($extension, $task): void {
                $admins = User::where('role', User::ROLE_ADMIN)
                    ->where('status', User::STATUS_ACTIVE)
                    ->get();

                Notification::sendNow($admins, new TaskPortalNotification(
                    'Task extension requested',
                    $task->member?->full_name.' requested more time for '.$task->task_title.'.',
                    route('admin.tasks.index', ['extension' => $extension->id]),
                ));
            });

            return $extension;
        });

        return back()->with('success', 'Your extension request was sent for administrator review.');
    }

    public function approve(TaskExtensionDecisionRequest $request, TaskExtensionRequest $taskExtensionRequest): RedirectResponse
    {
        return $this->decide($request, $taskExtensionRequest, TaskExtensionRequest::STATUS_APPROVED);
    }

    public function reject(TaskExtensionDecisionRequest $request, TaskExtensionRequest $taskExtensionRequest): RedirectResponse
    {
        return $this->decide($request, $taskExtensionRequest, TaskExtensionRequest::STATUS_REJECTED);
    }

    private function decide(TaskExtensionDecisionRequest $request, TaskExtensionRequest $extension, string $decision): RedirectResponse
    {
        $memberUserId = null;
        $newDeadline = null;
        $data = $request->validated();

        DB::transaction(function () use ($request, $extension, $decision, &$memberUserId, &$newDeadline, $data): void {
            $extension = TaskExtensionRequest::query()->lockForUpdate()->findOrFail($extension->id);
            if ($extension->status !== TaskExtensionRequest::STATUS_PENDING) {
                abort(409, 'This extension request has already been reviewed.');
            }

            $task = Task::query()->lockForUpdate()->findOrFail($extension->task_id);
            $extension->update([
                'status' => $decision,
                'reviewed_by_user_id' => $request->user()->id,
                'reviewed_at' => now(),
                'decision_note' => $data['decision_note'] ?? null,
            ]);

            if ($decision === TaskExtensionRequest::STATUS_APPROVED) {
                $newDeadline = $data['new_deadline'];
                $task->update([
                    'deadline' => $newDeadline,
                    'status' => $task->completed ? Task::STATUS_COMPLETED : Task::STATUS_ASSIGNED,
                ]);
            }

            $memberUserId = $task->member?->user?->id;
            AuditLog::record('task.extension_'.$decision, [
                'task_id' => $task->id,
                'member_id' => $extension->member_id,
                'new_deadline' => $newDeadline,
            ], $extension, $request->user());

            DB::afterCommit(function () use ($memberUserId, $decision, $newDeadline): void {
                $member = $memberUserId ? User::find($memberUserId) : null;
                if (! $member) {
                    return;
                }

                $message = $decision === TaskExtensionRequest::STATUS_APPROVED
                    ? 'Your task deadline extension was approved. The new deadline is '.now()->parse($newDeadline)->format('M d, Y · g:i A').'.'
                    : 'Your task deadline extension was declined. Review the administrator note on your task page.';

                Notification::sendNow($member, new TaskPortalNotification(
                    $decision === TaskExtensionRequest::STATUS_APPROVED ? 'Extension approved' : 'Extension declined',
                    $message,
                    route('tasks.index'),
                ));
            });
        });

        return back()->with('success', $decision === TaskExtensionRequest::STATUS_APPROVED ? 'Extension approved.' : 'Extension declined.');
    }
}
