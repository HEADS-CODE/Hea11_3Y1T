<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Puihaha | My Learning Tasks</title>
        @include('components.brand-head')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="tasks-page tasks-home">
        <div class="page-shell">
            @include('layouts._topbar')

            <main class="content-wrap">
                <section class="hero-section">
                    <div>
                        <span class="eyebrow">Your personal command center</span>
                        <h1>Make room for<br><em>what matters.</em></h1>
                        <p class="hero-copy">Your administrator assigns the next learning step. Keep your progress clear and request more time whenever you need it.</p>
                    </div>
                    <div class="hero-orbit" aria-hidden="true">
                        <span class="orbit-dot orbit-dot-one"></span>
                        <span class="orbit-dot orbit-dot-two"></span>
                        <span class="orbit-dot orbit-dot-three"></span>
                        <div class="orbit-center">✦</div>
                    </div>
                </section>

                @include('layouts._toasts')

                <section class="overview-grid" aria-label="Task overview">
                    <div class="stat-card stat-card-featured">
                        <span class="stat-label">Assigned tasks</span>
                        <strong data-task-summary="assigned">{{ $taskSummary['assigned'] }}</strong>
                        <span class="stat-note">in your learning space</span>
                        <span class="stat-shape" aria-hidden="true">◌</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">To do</span>
                        <strong data-task-summary="todo">{{ $taskSummary['todo'] }}</strong>
                        <span class="stat-note">ready when you are</span>
                        <span class="stat-icon icon-sun" aria-hidden="true">☼</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">Completed</span>
                        <strong data-task-summary="completed">{{ $taskSummary['completed'] }}</strong>
                        <span class="stat-note">small wins add up</span>
                        <span class="stat-icon icon-check" aria-hidden="true">✓</span>
                    </div>
                </section>

                @if (auth()->user()->member)
                    @include('components.weather-card', ['latitude' => $weatherLocation['latitude'], 'longitude' => $weatherLocation['longitude'], 'label' => $weatherLocation['label'], 'uses_default' => $weatherLocation['uses_default'], 'editUrl' => route('member.profile-change')])
                @endif

                <section id="task-section" class="workspace-grid task-member-grid">
                    <div class="panel create-panel">
                        <div class="panel-heading">
                            <div>
                                <span class="section-kicker">Administrator assignments</span>
                                <h2>How your tasks work</h2>
                            </div>
                            <span class="heading-spark" aria-hidden="true">✦</span>
                        </div>
                        <p class="panel-intro">Tasks are assigned and managed by the Puihaha administrator. You can mark your own work complete, but only an administrator can create, edit, archive, or change a deadline.</p>
                        <div class="task-guidance-list">
                            <div><span class="guidance-icon">✓</span><p><strong>Complete a task</strong><br>Use the checkmark when you finish an assignment.</p></div>
                            <div><span class="guidance-icon">i</span><p><strong>Need more time?</strong><br>Open the information button on an active task and send an extension request.</p></div>
                        </div>
                    </div>

                    <div class="panel list-panel">
                        <div class="panel-heading list-heading">
                            <div>
                                <span class="section-kicker">Priority &amp; immediate action</span>
                                <h2>Next steps <span class="count-badge">{{ $priorityTasks->count() }}</span></h2>
                            </div>
                            <a class="task-home-link" href="{{ route('member.tasks') }}">View all tasks in My Tasks <span>→</span></a>
                        </div>

                        @if ($priorityTasks->isNotEmpty())
                            <div class="task-list" data-task-list>
                                @foreach ($priorityTasks as $task)
                                    @php
                                        $status = $task->effectiveStatus();
                                        $pendingExtension = $task->extensionRequests->firstWhere('status', 'pending');
                                        $modalId = 'extension-modal-'.$task->id;
                                        $filterStatus = $task->completed ? 'completed' : ($pendingExtension ? 'extension_requested' : ($status === 'overdue' ? 'overdue' : 'todo'));
                                        $priority = $task->priority ?? \App\Models\Task::PRIORITY_MEDIUM;
                                    @endphp
                                    <article class="task-item {{ $task->completed ? 'is-completed' : '' }}" data-task-item data-task-id="{{ $task->id }}" data-task-status="{{ $filterStatus }}" data-task-priority="{{ $priority }}" data-task-completed="{{ $task->completed ? 'true' : 'false' }}">
                                        <form method="POST" action="{{ route('tasks.complete', $task) }}" class="task-status-form" data-task-completion-form>
                                            @csrf @method('PATCH')
                                            <button class="task-status-mark" type="submit" aria-label="{{ $task->completed ? 'Mark task active' : 'Mark task complete' }}" aria-pressed="{{ $task->completed ? 'true' : 'false' }}">{{ $task->completed ? '✓' : '' }}</button>
                                        </form>
                                        <div class="task-content">
                                            <div class="task-title-row">
                                                <h3 class="task-title-text">{{ $task->task_title }}</h3>
                                                <div class="task-badge-stack"><span class="task-priority task-priority-{{ $priority }}">{{ ucfirst($priority) }} priority</span><span class="task-status task-status-{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span></div>
                                            </div>
                                            <p>{{ $task->description ?: 'No instructions were added for this task.' }}</p>
                                            <div class="task-meta-row">
                                                <span class="task-date">Assigned {{ $task->created_at->format('M d, Y') }}</span>
                                                @if ($task->deadline)
                                                    <span class="task-date {{ $status === 'overdue' ? 'task-deadline-overdue' : '' }}">Due {{ $task->deadline->format('M d, Y · g:i A') }}</span>
                                                @else
                                                    <span class="task-date">No deadline</span>
                                                @endif
                                            </div>
                                            @if ($pendingExtension)
                                                <span class="task-extension-status">Extension request awaiting review</span>
                                            @endif
                                        </div>
                                        @if (! $task->completed && ! $pendingExtension)
                                            <button class="task-info-button" type="button" data-extension-open="{{ $modalId }}" aria-controls="{{ $modalId }}" aria-label="Request more time for {{ $task->task_title }}" title="Request a deadline extension">i</button>
                                        @endif
                                    </article>

                                    @if (! $task->completed && ! $pendingExtension)
                                        <div class="modal-backdrop task-extension-modal" id="{{ $modalId }}" data-extension-modal hidden>
                                            <div class="confirm-modal extension-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title">
                                                <div class="modal-heading-row">
                                                    <div><span class="section-kicker">Need more time?</span><h2 id="{{ $modalId }}-title">Request an extension</h2></div>
                                                    <button type="button" class="modal-close" data-extension-cancel aria-label="Close extension request">×</button>
                                                </div>
                                                <p>Tell the administrator why you need more time for <strong>{{ $task->task_title }}</strong>. The deadline changes only after approval.</p>
                                                <form method="POST" action="{{ route('tasks.extension-requests.store', $task) }}" class="extension-form">
                                                    @csrf
                                                    <div class="field-group"><label for="reason_{{ $task->id }}">Reason <span>*</span></label><textarea id="reason_{{ $task->id }}" name="reason" rows="5" maxlength="1000" required placeholder="Explain what is affecting your schedule. You may mention a preferred date here."></textarea></div>
                                                    <div class="modal-actions"><button class="button button-ghost" type="button" data-extension-cancel>Cancel</button><button class="button button-primary" type="submit">Send request</button></div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="empty-filter" data-empty-filter hidden>
                                <span>✦</span>
                                <p>No tasks match this filter.</p>
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-illustration" aria-hidden="true"><span>✦</span></div>
                                <h3>Your learning list is wide open.</h3>
                                <p>An administrator will assign your first task here when it is ready.</p>
                            </div>
                        @endif
                        <section class="insight-panel" data-insight-panel hidden aria-label="Task insights"><div class="insights-toolbar"><div><span class="section-kicker">A clearer picture</span><h2>Insights</h2></div><div class="insight-switch"><button type="button" class="is-active" data-insight-mode="cards">Cards</button><button type="button" data-insight-mode="charts">Charts</button></div></div><div class="insight-cards" data-insight-cards><div class="insight-card"><span class="stat-label">Total tasks</span><strong>{{ $tasks->count() }}</strong></div><div class="insight-card"><span class="stat-label">To do</span><strong>{{ $tasks->where('completed', false)->count() }}</strong></div><div class="insight-card"><span class="stat-label">Completed</span><strong>{{ $tasks->where('completed', true)->count() }}</strong></div></div><div class="insight-charts" data-insight-charts hidden><div class="insight-chart-card"><h3>Status</h3><svg class="status-donut" viewBox="0 0 42 42" role="img" aria-label="Task status chart"><circle class="donut-track" cx="21" cy="21" r="15.9155"></circle><circle class="donut-done" cx="21" cy="21" r="15.9155" pathLength="100" stroke-dasharray="{{ $tasks->count() ? round(($tasks->where('completed', true)->count() / $tasks->count()) * 100, 1) : 0 }} {{ $tasks->count() ? round(($tasks->where('completed', false)->count() / $tasks->count()) * 100, 1) : 100 }}"></circle></svg><span>{{ $tasks->where('completed', true)->count() }} done · {{ $tasks->where('completed', false)->count() }} active</span></div><div class="insight-chart-card"><h3>Created in the last 7 days</h3><div class="bar-chart">@foreach($taskByDay as $day => $count)<div class="bar-column"><span class="bar-value">{{ $count }}</span><div class="bar" style="height: {{ $count ? min(100, 20 + ($count * 20)) : 4 }}%"></div><small>{{ $day }}</small></div>@endforeach</div></div><div class="insight-chart-card"><h3>Completed in the last 14 days</h3><div class="line-chart" aria-label="Completed tasks by day">@foreach($completedByDay as $day => $count)<span class="line-point" style="bottom: {{ $count ? min(92, 20 + ($count * 20)) : 8 }}%" title="{{ $day }}: {{ $count }}"></span>@endforeach</div><div class="line-labels">@foreach($completedByDay as $day => $count)<small>{{ $day }}</small>@endforeach</div></div></div></section>
                    </div>
                </section>

                <section id="insights" class="insight-panel task-insights-panel" aria-label="Task insights">
                    @php
                        $completedCount = $tasks->where('completed', true)->count();
                        $remainingCount = $tasks->where('completed', false)->count();
                        $overdueCount = $tasks->filter(fn ($task) => ! $task->completed && $task->effectiveStatus() === 'overdue')->count();
                        $completionPercent = $tasks->count() ? round(($completedCount / $tasks->count()) * 100) : 0;
                    @endphp
                    <div class="insights-toolbar">
                        <div><span class="section-kicker">A clearer picture</span><h2>Insights</h2><p class="panel-intro">A quick view of your assigned work and recent progress.</p></div>
                    </div>
                    <div class="insight-charts" data-insight-charts>
                        <div class="insight-chart-card">
                            <h3>Completion status</h3>
                            <svg class="status-donut" data-completion-donut viewBox="0 0 42 42" role="img" aria-label="Task completion chart"><circle class="donut-track" cx="21" cy="21" r="15.9155"></circle><circle class="donut-done" cx="21" cy="21" r="15.9155" pathLength="100" stroke-dasharray="{{ $completionPercent }} {{ 100 - $completionPercent }}"></circle></svg>
                            <div class="insight-status-summary"><strong data-completion-percent>{{ $completionPercent }}%</strong><div><span class="insight-pill insight-pill-complete"><span data-completion-count>{{ $completedCount }}</span> completed</span><span class="insight-pill insight-pill-remaining"><span data-remaining-count>{{ $remainingCount }}</span> remaining</span><span class="insight-pill insight-pill-overdue" data-overdue-pill @if(! $overdueCount) hidden @endif><span data-overdue-count>{{ $overdueCount }}</span> overdue</span></div></div>
                        </div>
                        <div class="insight-chart-card">
                            <h3>Assignments in the last 7 days</h3>
                            <div class="bar-chart">@foreach($taskByDay as $day => $count)<div class="bar-column"><span class="bar-value">{{ $count }}</span><div class="bar" style="height: {{ $count ? min(100, 20 + ($count * 20)) : 4 }}%"></div><small>{{ $day }}</small></div>@endforeach</div>
                        </div>
                        <div class="insight-chart-card">
                            <h3>Completions in the last 14 days</h3>
                            <div class="line-chart" aria-label="Completed tasks by day">@foreach($completedByDay as $day => $count)<span class="line-point" data-completion-point data-day="{{ $day }}" style="bottom: {{ $count ? min(82, 16 + ($count * 18)) : 8 }}%" data-tooltip="{{ $day }}: {{ $count }} completed" title="{{ $day }}: {{ $count }} completed"></span>@endforeach</div><div class="line-labels">@foreach($completedByDay as $day => $count)<small>{{ $day }}</small>@endforeach</div>
                        </div>
                    </div>
                </section>

                <footer class="page-footer">
                    <span>Built with Laravel, Eloquent &amp; MySQL</span>
                    <span class="footer-line"></span>
                    <span>One learning step at a time.</span>
                </footer>
            </main>
        </div>
    </body>
</html>
