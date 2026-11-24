@extends('layouts.puihaha')

@section('title', 'Learning Tasks | Puihaha')

@section('content')
<div class="page-intro admin-task-intro">
    <div>
        <span class="eyebrow">Administrator workspace</span>
        <h1>Learning tasks <em>assigned.</em></h1>
        <p>Create clear assignments, set realistic deadlines, and review extension requests from one place.</p>
    </div>
    <a class="button button-primary" href="{{ route('admin.tasks.create') }}"><span>＋</span> Assign task</a>
</div>

@include('layouts._toasts')

<form class="directory-toolbar admin-task-filters" method="GET" action="{{ route('admin.tasks.index') }}">
    <input type="search" name="q" value="{{ $search }}" placeholder="Search task, member, or number" aria-label="Search tasks">
    <select name="status" aria-label="Filter tasks by status">
        <option value="">All active tasks</option>
        <option value="assigned" @selected($status === 'assigned')>Assigned</option>
        <option value="in_progress" @selected($status === 'in_progress')>In progress</option>
        <option value="completed" @selected($status === 'completed')>Completed</option>
    </select>
    <select name="priority" aria-label="Filter tasks by priority">
        <option value="">All priorities</option>
        <option value="high" @selected($priority === 'high')>High priority</option>
        <option value="medium" @selected($priority === 'medium')>Medium priority</option>
        <option value="low" @selected($priority === 'low')>Low priority</option>
    </select>
    <select name="member_id" aria-label="Filter tasks by member">
        <option value="">All members</option>
        @foreach ($members as $member)
            <option value="{{ $member->id }}" @selected((int) $memberId === $member->id)>{{ $member->full_name }} · {{ $member->membership_number }}</option>
        @endforeach
    </select>
    <label class="admin-task-checkbox"><input type="checkbox" name="show_archived" value="1" @checked(request()->boolean('show_archived'))> Show archived</label>
    <button class="button button-dark" type="submit">Filter</button>
    @if ($search || $status || $priority || $memberId || request()->boolean('show_archived'))
        <a class="clear-link" href="{{ route('admin.tasks.index') }}">Clear</a>
    @endif
</form>

@if ($pendingExtensions->isNotEmpty())
    <section class="panel task-extension-review">
        <div class="panel-heading">
            <div><span class="eyebrow">Needs review</span><h2>Deadline extension requests</h2></div>
            <span class="result-count">{{ $pendingExtensions->count() }} pending</span>
        </div>
        <div class="extension-review-grid">
            @foreach ($pendingExtensions as $extension)
                <article class="task-extension-card">
                    <div class="task-extension-card-heading">
                        <div><strong>{{ $extension->task->task_title }}</strong><span>{{ $extension->member->full_name }}</span></div>
                        <span class="status-badge status-pending">Pending</span>
                    </div>
                    <p>{{ $extension->reason }}</p>
                    <dl class="task-extension-details"><div><dt>Current deadline</dt><dd>{{ $extension->task->deadline?->format('M d, Y · g:i A') ?: 'No deadline' }}</dd></div><div><dt>Member’s note</dt><dd>Administrator sets the new deadline</dd></div></dl>
                    <div class="extension-review-actions">
                        <form method="POST" action="{{ route('admin.task-extension-requests.approve', $extension) }}">@csrf<input type="datetime-local" name="new_deadline" min="{{ now()->addDay()->format('Y-m-d\\TH:i') }}" required><input type="text" name="decision_note" placeholder="Optional approval note"><button class="button button-primary" type="submit">Approve</button></form>
                        <form method="POST" action="{{ route('admin.task-extension-requests.reject', $extension) }}">@csrf<input type="text" name="decision_note" placeholder="Optional reason"><button class="button button-danger" type="submit">Decline</button></form>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif

<section class="panel table-panel admin-task-panel">
    <div class="panel-heading">
        <div><span class="eyebrow">The assignment register</span><h2>All learning tasks</h2></div>
        <span class="result-count">{{ $tasks->count() }} results</span>
    </div>
    @if ($tasks->isNotEmpty())
        <div class="table-scroll">
            <table class="members-table task-admin-table">
                <thead><tr><th>Assignment</th><th>Member</th><th>Deadline</th><th>Status</th><th>Progress</th><th></th></tr></thead>
                <tbody>
                    @foreach ($tasks as $task)
                        @php $latestExtension = $task->extensionRequests->first(); $effectiveStatus = $task->effectiveStatus(); @endphp
                        <tr>
                            <td><strong class="task-admin-title">{{ $task->task_title }}</strong><span class="task-priority task-priority-{{ $task->priority ?? 'medium' }}">{{ ucfirst($task->priority ?? 'medium') }} priority</span><span class="member-email">{{ $task->description ?: 'No instructions added.' }}</span>@if($latestExtension && $latestExtension->status !== 'pending')<span class="member-email">Latest extension: {{ ucfirst($latestExtension->status) }}</span>@endif</td>
                            <td><a class="member-name task-member-link" href="{{ route('members.show', $task->member) }}">{{ $task->member?->full_name ?: 'Unlinked member' }}</a><span class="member-email">{{ $task->member?->membership_number }}</span></td>
                            <td>{{ $task->deadline?->format('M d, Y · g:i A') ?: 'No deadline' }}</td>
                            <td><span class="status-badge status-{{ $effectiveStatus === 'overdue' ? 'inactive' : ($effectiveStatus === 'completed' ? 'active' : 'pending') }}">{{ ucfirst(str_replace('_', ' ', $effectiveStatus)) }}</span></td>
                            <td>{{ $task->completed ? 'Completed' : 'Open' }}</td>
                            <td><div class="row-actions"><a href="{{ route('admin.tasks.edit', $task) }}">Edit</a>@if(!$task->archived_at)<form method="POST" action="{{ route('admin.tasks.archive', $task) }}" data-confirm="Archive this task for the member?"><input type="hidden" name="_token" value="{{ csrf_token() }}"><button type="submit">Archive</button></form>@endif</div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state"><span class="empty-icon">✦</span><h3>No tasks found</h3><p>Assign the first learning task or adjust your filters.</p><a class="button button-primary" href="{{ route('admin.tasks.create') }}">Assign a task</a></div>
    @endif
</section>
@endsection
