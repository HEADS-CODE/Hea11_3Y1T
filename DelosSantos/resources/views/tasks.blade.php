<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Taskflow | My Tasks</title>
        <script>if (localStorage.getItem('puihaha-theme') === 'dark') document.documentElement.classList.add('dark-mode');</script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="tasks-page">
        <div class="page-shell">
            @include('layouts._topbar')

            <main class="content-wrap">
                <section class="hero-section">
                    <div>
                        <span class="eyebrow">Your personal command center</span>
                        <h1>Make room for<br><em>what matters.</em></h1>
                        <p class="hero-copy">Capture the next thing, keep your momentum, and turn a busy day into a clear plan.</p>
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
                        <span class="stat-label">Total tasks</span>
                        <strong>{{ $tasks->count() }}</strong>
                        <span class="stat-note">in your workspace</span>
                        <span class="stat-shape" aria-hidden="true">◌</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">To do</span>
                        <strong>{{ $tasks->where('completed', false)->count() }}</strong>
                        <span class="stat-note">ready when you are</span>
                        <span class="stat-icon icon-sun" aria-hidden="true">☼</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">Completed</span>
                        <strong>{{ $tasks->where('completed', true)->count() }}</strong>
                        <span class="stat-note">small wins add up</span>
                        <span class="stat-icon icon-check" aria-hidden="true">✓</span>
                    </div>
                </section>

                <section id="task-section" class="workspace-grid">
                    <div class="panel create-panel">
                        <div class="panel-heading">
                            <div>
                                <span class="section-kicker">New entry</span>
                                <h2>Add a task</h2>
                            </div>
                            <span class="heading-spark" aria-hidden="true">✦</span>
                        </div>
                        <p class="panel-intro">Give your next step a name. You can add the details when you are ready.</p>

                        <form action="{{ route('tasks.store') }}" method="POST" class="task-form" data-task-form>
                            @csrf
                            <div class="field-group">
                                <label for="task_title">Task title <span>*</span></label>
                                <input
                                    type="text"
                                    id="task_title"
                                    name="task_title"
                                    value="{{ old('task_title') }}"
                                    placeholder="e.g. Finish the project presentation"
                                    maxlength="255"
                                    required
                                >
                                @error('task_title')
                                    <span class="field-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field-group">
                                <div class="label-row">
                                    <label for="description">Description <span class="optional">Optional</span></label>
                                    <span class="character-count" data-character-count>0 / 500</span>
                                </div>
                                <textarea id="description" name="description" rows="5" maxlength="500" placeholder="Add a little context, a link, or your definition of done...">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="field-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="button button-primary button-full" data-submit-button>
                                <span class="button-plus">+</span> Add task
                            </button>
                        </form>
                    </div>

                    <div class="panel list-panel">
                        <div class="panel-heading list-heading">
                            <div>
                                <span class="section-kicker">Your workspace</span>
                                <h2>Saved tasks <span class="count-badge">{{ $tasks->count() }}</span></h2>
                            </div>
                            <div class="filter-tabs" role="tablist" aria-label="Task views">
                                <button class="filter-tab is-active" type="button" data-filter="all" role="tab" aria-selected="true">All</button>
                                <button class="filter-tab" type="button" data-filter="active" role="tab" aria-selected="false">Active</button>
                                <button class="filter-tab" type="button" data-filter="completed" role="tab" aria-selected="false">Done</button>
                                <button class="filter-tab" type="button" data-insight-tab="insights" role="tab" aria-selected="false">Insights</button>
                            </div>
                        </div>

                        @if ($tasks->isNotEmpty())
                            <div class="task-list" data-task-list>
                                @foreach ($tasks as $task)
                                    <article class="task-item {{ $task->completed ? 'is-completed' : '' }}" data-task-item data-status="{{ $task->completed ? 'completed' : 'active' }}">
                                        <form method="POST" action="{{ route('tasks.complete', $task) }}" class="task-status-form">@csrf @method('PATCH')<button class="task-status-mark" type="submit" aria-label="{{ $task->completed ? 'Mark task active' : 'Mark task complete' }}" aria-pressed="{{ $task->completed ? 'true' : 'false' }}">{{ $task->completed ? '✓' : '' }}</button></form>
                                        <div class="task-content">
                                            <div class="task-title-row">
                                                <h3>{{ $task->task_title }}</h3>
                                                <span class="task-status">{{ $task->completed ? 'Completed' : 'In progress' }}</span>
                                            </div>
                                            <p>{{ $task->description ?: 'No description added yet.' }}</p>
                                            <span class="task-date">Added {{ $task->created_at->format('M d, Y') }}</span>
                                        </div>
                                        <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="task-delete-form" data-confirm="Remove this task?">@csrf @method('DELETE')<button class="task-delete-button" type="submit" title="Remove task" aria-label="Remove {{ $task->task_title }}">×</button></form>
                                    </article>
                                @endforeach
                            </div>
                            <div class="empty-filter" data-empty-filter hidden>
                                <span>✦</span>
                                <p>No tasks match this filter.</p>
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-illustration" aria-hidden="true"><span>✦</span></div>
                                <h3>Your list is wide open.</h3>
                                <p>Add your first task and start turning intention into progress.</p>
                            </div>
                        @endif
                        <section class="insight-panel" data-insight-panel hidden aria-label="Task insights"><div class="insights-toolbar"><div><span class="section-kicker">A clearer picture</span><h2>Insights</h2></div><div class="insight-switch"><button type="button" class="is-active" data-insight-mode="cards">Cards</button><button type="button" data-insight-mode="charts">Charts</button></div></div><div class="insight-cards" data-insight-cards><div class="insight-card"><span class="stat-label">Total tasks</span><strong>{{ $tasks->count() }}</strong></div><div class="insight-card"><span class="stat-label">To do</span><strong>{{ $tasks->where('completed', false)->count() }}</strong></div><div class="insight-card"><span class="stat-label">Completed</span><strong>{{ $tasks->where('completed', true)->count() }}</strong></div></div><div class="insight-charts" data-insight-charts hidden><div class="insight-chart-card"><h3>Status</h3><svg class="status-donut" viewBox="0 0 42 42" role="img" aria-label="Task status chart"><circle class="donut-track" cx="21" cy="21" r="15.9155"></circle><circle class="donut-done" cx="21" cy="21" r="15.9155" pathLength="100" stroke-dasharray="{{ $tasks->count() ? round(($tasks->where('completed', true)->count() / $tasks->count()) * 100, 1) : 0 }} {{ $tasks->count() ? round(($tasks->where('completed', false)->count() / $tasks->count()) * 100, 1) : 100 }}"></circle></svg><span>{{ $tasks->where('completed', true)->count() }} done · {{ $tasks->where('completed', false)->count() }} active</span></div><div class="insight-chart-card"><h3>Created in the last 7 days</h3><div class="bar-chart">@foreach($taskByDay as $day => $count)<div class="bar-column"><span class="bar-value">{{ $count }}</span><div class="bar" style="height: {{ $count ? min(100, 20 + ($count * 20)) : 4 }}%"></div><small>{{ $day }}</small></div>@endforeach</div></div><div class="insight-chart-card"><h3>Completed in the last 14 days</h3><div class="line-chart" aria-label="Completed tasks by day">@foreach($completedByDay as $day => $count)<span class="line-point" style="bottom: {{ $count ? min(92, 20 + ($count * 20)) : 8 }}%" title="{{ $day }}: {{ $count }}"></span>@endforeach</div><div class="line-labels">@foreach($completedByDay as $day => $count)<small>{{ $day }}</small>@endforeach</div></div></div></section>
                    </div>
                </section>

                <footer class="page-footer">
                    <span>Built with Laravel, Eloquent &amp; MySQL</span>
                    <span class="footer-line"></span>
                    <span>One task at a time.</span>
                </footer>
            </main>
        </div>
    </body>
</html>
