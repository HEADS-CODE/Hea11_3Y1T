<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $tasks = Task::where('member_id', auth()->user()->member_id)->latest()->get();
        $taskByDay = collect(range(6, 0))->mapWithKeys(function (int $daysAgo): array {
            $date = now()->subDays($daysAgo)->startOfDay();

            return [$date->format('M d') => Task::where('member_id', auth()->user()->member_id)->whereDate('created_at', $date)->count()];
        });
        $completedByDay = collect(range(13, 0))->mapWithKeys(function (int $daysAgo): array {
            $date = now()->subDays($daysAgo)->startOfDay();

            return [$date->format('M d') => Task::where('member_id', auth()->user()->member_id)->whereDate('completed_at', $date)->count()];
        });

        return view('tasks', compact('tasks', 'taskByDay', 'completedByDay'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'task_title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['member_id'] = auth()->user()->member_id;
        Task::create($validated);

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task added successfully.');
    }

    public function complete(Task $task): RedirectResponse
    {
        abort_unless($task->member_id === auth()->user()->member_id, 404);
        $task->update([
            'completed' => ! $task->completed,
            'completed_at' => $task->completed ? null : now(),
        ]);

        return back()->with('success', $task->completed ? 'Task marked complete.' : 'Task moved back to active.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        abort_unless($task->member_id === auth()->user()->member_id, 404);
        $task->delete();

        return back()->with('success', 'Task removed from your workspace.');
    }
}
