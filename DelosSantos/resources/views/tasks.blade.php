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
            <header class="topbar">
                <a class="brand" href="{{ route('tasks.index') }}" aria-label="Taskflow home">
                    <span class="brand-mark"><span></span><span></span><span></span></span>
                    <span>taskflow</span>
                </a>
                <button class="theme-toggle" type="button" data-theme-toggle aria-pressed="false" title="Switch to dark mode"><span class="theme-toggle-icon" data-theme-icon aria-hidden="true">☾</span><span data-theme-label>Dark mode</span></button>
            </header>

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

                @if (session('success'))
                    <div class="alert alert-success" role="status">
                        <span class="alert-icon">✓</span>
                        <span>{{ session('success') }}</span>
                        <button type="button" class="alert-close" aria-label="Dismiss message">×</button>
                    </div>
                @endif

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

                <section class="workspace-grid">
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
                                    autofocus
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
                            <div class="filter-tabs" role="tablist" aria-label="Filter tasks">
                                <button class="filter-tab is-active" type="button" data-filter="all" role="tab" aria-selected="true">All</button>
                                <button class="filter-tab" type="button" data-filter="active" role="tab" aria-selected="false">Active</button>
                                <button class="filter-tab" type="button" data-filter="completed" role="tab" aria-selected="false">Done</button>
                            </div>
                        </div>

                        @if ($tasks->isNotEmpty())
                            <div class="task-list" data-task-list>
                                @foreach ($tasks as $task)
                                    <article class="task-item {{ $task->completed ? 'is-completed' : '' }}" data-task-item data-status="{{ $task->completed ? 'completed' : 'active' }}">
                                        <span class="task-status-mark" aria-hidden="true">{{ $task->completed ? '✓' : '' }}</span>
                                        <div class="task-content">
                                            <div class="task-title-row">
                                                <h3>{{ $task->task_title }}</h3>
                                                <span class="task-status">{{ $task->completed ? 'Completed' : 'In progress' }}</span>
                                            </div>
                                            <p>{{ $task->description ?: 'No description added yet.' }}</p>
                                            <span class="task-date">Added {{ $task->created_at->format('M d, Y') }}</span>
                                        </div>
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
