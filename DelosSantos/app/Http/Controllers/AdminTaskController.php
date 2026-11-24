<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminTaskRequest;
use App\Models\AuditLog;
use App\Models\Member;
use App\Models\Task;
use App\Models\TaskExtensionRequest;
use App\Models\User;
use App\Notifications\TaskPortalNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AdminTaskController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $priority = $request->string('priority')->toString();
        $search = trim($request->string('q')->toString());
        $memberId = $request->integer('member_id');

        $tasks = Task::with([
            'member',
            'creator',
            'extensionRequests' => fn ($query) => $query->latest(),
        ])
            ->when(! $request->boolean('show_archived'), fn ($query) => $query->whereNull('archived_at'))
            ->when(in_array($status, [Task::STATUS_ASSIGNED, Task::STATUS_IN_PROGRESS, Task::STATUS_COMPLETED], true), fn ($query) => $query->where('status', $status))
            ->when(in_array($priority, Task::PRIORITIES, true), fn ($query) => $query->where('priority', $priority))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $like = '%'.$search.'%';
                    $query->where('task_title', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhereHas('member', function ($memberQuery) use ($like): void {
                            $memberQuery->where('first_name', 'like', $like)
                                ->orWhere('last_name', 'like', $like)
                                ->orWhere('email', 'like', $like)
                                ->orWhere('membership_number', 'like', $like);
                        });
                });
            })
            ->when($memberId > 0, fn ($query) => $query->where('member_id', $memberId))
            ->latest()
            ->get();

        $members = Member::query()
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $pendingExtensions = TaskExtensionRequest::with(['task', 'member'])
            ->where('status', TaskExtensionRequest::STATUS_PENDING)
            ->latest()
            ->get();

        return view('tasks.admin.index', compact('tasks', 'members', 'pendingExtensions', 'status', 'priority', 'search', 'memberId'));
    }

    public function create(): View
    {
        $members = Member::query()
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('tasks.admin.create', compact('members'));
    }

    public function store(AdminTaskRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $memberUserIds = [];
        $taskData = $data;
        unset($taskData['member_id']);

        DB::transaction(function () use ($data, $taskData, &$memberUserIds): void {
            $members = $data['member_id'] === 'all'
                ? Member::with('user')->where('status', 'active')->whereNull('archived_at')->get()
                : Member::with('user')->whereKey($data['member_id'])->get();

            foreach ($members as $member) {
                $task = Task::create([
                    ...$taskData,
                    'member_id' => $member->id,
                    'created_by_user_id' => auth()->id(),
                    'status' => Task::STATUS_ASSIGNED,
                    'published_at' => now(),
                ]);

                if ($member->user?->id) {
                    $memberUserIds[] = $member->user->id;
                }

                AuditLog::record('task.created', [
                    'task_id' => $task->id,
                    'member_id' => $task->member_id,
                    'deadline' => $task->deadline?->toIso8601String(),
                ], $task, auth()->user());
            }

            DB::afterCommit(function () use ($memberUserIds): void {
                $members = User::whereIn('id', $memberUserIds)->get();
                Notification::sendNow($members, new TaskPortalNotification(
                    'New task assigned',
                    'An administrator assigned a new learning task to you.',
                    route('tasks.index'),
                ));
            });
        });

        $count = $data['member_id'] === 'all' ? count($memberUserIds) : 1;

        return redirect()->route('admin.tasks.index')->with('success', $count === 1 ? 'Task assigned successfully.' : "Task assigned to {$count} active members.");
    }

    public function edit(Task $task): View
    {
        abort_if($task->archived_at !== null, 404);

        $members = Member::query()
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('tasks.admin.edit', compact('task', 'members'));
    }

    public function update(AdminTaskRequest $request, Task $task): RedirectResponse
    {
        abort_if($task->archived_at !== null, 404);
        $data = $request->validated();
        $memberUserId = null;
        $memberId = $data['member_id'];
        unset($data['member_id']);

        DB::transaction(function () use ($task, $data, $memberId, &$memberUserId): void {
            $task->fill([...$data, 'member_id' => $memberId])->save();
            $memberUserId = $task->fresh(['member.user'])->member?->user?->id;

            AuditLog::record('task.updated', [
                'task_id' => $task->id,
                'member_id' => $task->member_id,
                'deadline' => $task->deadline?->toIso8601String(),
            ], $task, auth()->user());

            DB::afterCommit(function () use ($memberUserId): void {
                $member = $memberUserId ? User::find($memberUserId) : null;
                if ($member) {
                    Notification::sendNow($member, new TaskPortalNotification(
                        'Task updated',
                        'An administrator updated one of your learning tasks.',
                        route('tasks.index'),
                    ));
                }
            });
        });

        return redirect()->route('admin.tasks.index')->with('success', 'Task updated successfully.');
    }

    public function archive(Task $task): RedirectResponse
    {
        abort_if($task->archived_at !== null, 404);
        $memberUserId = null;

        DB::transaction(function () use ($task, &$memberUserId): void {
            $task->update([
                'status' => Task::STATUS_ARCHIVED,
                'archived_at' => now(),
            ]);
            $memberUserId = $task->fresh(['member.user'])->member?->user?->id;
            AuditLog::record('task.archived', ['task_id' => $task->id, 'member_id' => $task->member_id], $task, auth()->user());

            DB::afterCommit(function () use ($memberUserId): void {
                $member = $memberUserId ? User::find($memberUserId) : null;
                if ($member) {
                    Notification::sendNow($member, new TaskPortalNotification(
                        'Task archived',
                        'One of your learning tasks was archived by an administrator.',
                        route('tasks.index'),
                    ));
                }
            });
        });

        return redirect()->route('admin.tasks.index')->with('success', 'Task archived.');
    }

}
