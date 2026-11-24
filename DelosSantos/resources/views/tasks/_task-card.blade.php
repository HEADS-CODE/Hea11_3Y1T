@php
    $status = $task->effectiveStatus();
    $pendingExtension = $task->extensionRequests->firstWhere('status', 'pending');
    $modalId = 'extension-modal-'.$task->id.'-'.$cardContext;
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
        @if ($pendingExtension)<span class="task-extension-status">Extension request awaiting review</span>@endif
    </div>
    @if (! $task->completed && ! $pendingExtension)
        <button class="task-info-button" type="button" data-extension-open="{{ $modalId }}" aria-controls="{{ $modalId }}" aria-label="Request more time for {{ $task->task_title }}" title="Request a deadline extension">i</button>
    @endif
</article>
@if (! $task->completed && ! $pendingExtension)
    <div class="modal-backdrop task-extension-modal" id="{{ $modalId }}" data-extension-modal hidden>
        <div class="confirm-modal extension-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title">
            <div class="modal-heading-row"><div><span class="section-kicker">Need more time?</span><h2 id="{{ $modalId }}-title">Request an extension</h2></div><button type="button" class="modal-close" data-extension-cancel aria-label="Close extension request">×</button></div>
            <p>Tell the administrator why you need more time for <strong>{{ $task->task_title }}</strong>. The deadline changes only after approval.</p>
            <form method="POST" action="{{ route('tasks.extension-requests.store', $task) }}" class="extension-form">@csrf<div class="field-group"><label for="reason_{{ $modalId }}">Reason <span>*</span></label><textarea id="reason_{{ $modalId }}" name="reason" rows="5" maxlength="1000" required placeholder="Explain what is affecting your schedule. You may mention a preferred date here."></textarea></div><div class="modal-actions"><button class="button button-ghost" type="button" data-extension-cancel>Cancel</button><button class="button button-primary" type="submit">Send request</button></div></form>
        </div>
    </div>
@endif
