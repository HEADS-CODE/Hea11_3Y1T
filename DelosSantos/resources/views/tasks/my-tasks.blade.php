@extends('layouts.puihaha')

@section('title', 'My Tasks | Puihaha')

@section('content')
<div class="page-intro task-workspace-intro">
    <div><span class="eyebrow">Project-management workspace</span><h1>My <em>tasks.</em></h1><p>Search, prioritize, and complete every administrator-assigned learning step in one focused workspace.</p></div>
    <a class="button button-ghost" href="{{ route('tasks.index') }}">← Home</a>
</div>

<div class="task-workspace-shell" data-task-workspace data-page-size="8">
    <section class="panel task-insights-panel task-insights-grid-panel" aria-label="Task insights">
        <div class="insights-toolbar"><div><span class="section-kicker">A clearer picture</span><h2>Progress at a glance</h2><p class="panel-intro">Your assignments and momentum, updated as you work.</p></div></div>
        <div class="insight-charts">
            <div class="insight-chart-card"><h3>Completion status</h3><svg class="status-donut" data-completion-donut viewBox="0 0 42 42" role="img" aria-label="Task completion chart"><circle class="donut-track" cx="21" cy="21" r="15.9155"></circle><circle class="donut-done" cx="21" cy="21" r="15.9155" pathLength="100" stroke-dasharray="{{ $taskSummary['completion_percent'] }} {{ 100 - $taskSummary['completion_percent'] }}"></circle></svg><div class="insight-status-summary"><strong data-completion-percent>{{ $taskSummary['completion_percent'] }}%</strong><div><span class="insight-pill insight-pill-complete"><span data-completion-count>{{ $taskSummary['completed'] }}</span> completed</span><span class="insight-pill insight-pill-remaining"><span data-remaining-count>{{ $taskSummary['todo'] }}</span> remaining</span><span class="insight-pill insight-pill-overdue" data-overdue-pill @if(! $taskSummary['overdue']) hidden @endif><span data-overdue-count>{{ $taskSummary['overdue'] }}</span> overdue</span></div></div></div>
            <div class="insight-chart-card"><h3>Assignments in the last 7 days</h3><div class="bar-chart">@foreach($taskByDay as $day => $count)<div class="bar-column"><span class="bar-value">{{ $count }}</span><div class="bar" style="height: {{ $count ? min(100, 20 + ($count * 20)) : 4 }}%"></div><small>{{ $day }}</small></div>@endforeach</div></div>
            <div class="insight-chart-card"><h3>Completions in the last 14 days</h3><div class="line-chart" aria-label="Completed tasks by day">@foreach($completedByDay as $day => $count)<span class="line-point" data-completion-point data-day="{{ $day }}" style="bottom: {{ $count ? min(82, 16 + ($count * 18)) : 8 }}%" data-tooltip="{{ $day }}: {{ $count }} completed" title="{{ $day }}: {{ $count }} completed"></span>@endforeach</div><div class="line-labels">@foreach($completedByDay as $day => $count)<small>{{ $day }}</small>@endforeach</div></div>
        </div>
    </section>

    <section class="overview-grid task-workspace-summary" aria-label="Live task summary">
        <div class="stat-card stat-card-featured"><span class="stat-label">Assigned tasks</span><strong data-task-summary="assigned">{{ $taskSummary['assigned'] }}</strong><span class="stat-note">in your workspace</span></div>
        <div class="stat-card"><span class="stat-label">To do</span><strong data-task-summary="todo">{{ $taskSummary['todo'] }}</strong><span class="stat-note">ready when you are</span></div>
        <div class="stat-card"><span class="stat-label">Completed</span><strong data-task-summary="completed">{{ $taskSummary['completed'] }}</strong><span class="stat-note">small wins add up</span></div>
    </section>

    <section class="panel task-list-workspace">
        <div class="panel-heading"><div><span class="section-kicker">Your learning space</span><h2>All assigned tasks</h2></div><span class="result-count" data-task-result-count>{{ $tasks->count() }} tasks</span></div>
        <form class="task-filter-bar" data-task-filter-form onsubmit="return false">
            <label class="task-search-field"><span class="sr-only">Search tasks</span><input type="search" data-task-search placeholder="Search task title or instructions" autocomplete="off"></label>
            <select data-task-priority aria-label="Filter by priority"><option value="all">All priorities</option><option value="high">High</option><option value="medium">Medium</option><option value="low">Low</option></select>
            <select data-task-status aria-label="Filter by status"><option value="all">All statuses</option><option value="todo">To do</option><option value="overdue">Overdue</option><option value="completed">Completed</option><option value="extension_requested">Extension requested</option></select>
            <button class="button button-ghost" type="button" data-task-filter-clear>Clear</button>
        </form>
        @if($tasks->isNotEmpty())
            <div class="task-list task-workspace-list" data-task-list>
                @foreach($tasks as $task)
                    @include('tasks._task-card', ['task' => $task, 'cardContext' => 'workspace'])
                @endforeach
            </div>
            <div class="empty-filter" data-task-empty hidden><span>✦</span><p>No tasks match your search and filters.</p></div>
            <nav class="task-pagination" data-task-pagination aria-label="Task pages"></nav>
        @else
            <div class="empty-state"><span class="empty-icon">✦</span><h3>Your workspace is ready.</h3><p>An administrator will assign your first learning task here.</p></div>
        @endif
    </section>
</div>
@endsection
