<form method="POST" action="{{ $formAction }}" class="admin-task-form">
    @csrf
    @if ($formMethod !== 'POST') @method($formMethod) @endif
    <div class="form-grid">
        <div class="field-group form-span-2">
            <label for="member_id">Assign to member <span>*</span></label>
            <select id="member_id" name="member_id" required>
                <option value="">Choose an active member</option>
                @if (! $task)
                    <option value="all" @selected(old('member_id') === 'all')>All active members</option>
                @endif
                @foreach ($members as $member)
                    <option value="{{ $member->id }}" @selected((string) old('member_id', $task?->member_id) === (string) $member->id)>{{ $member->full_name }} · {{ $member->membership_number }}</option>
                @endforeach
            </select>
            @if (! $task)<small class="field-hint">Choose “All active members” to create one individual task record per active, non-archived member.</small>@endif
            @error('member_id')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field-group">
            <label for="priority">Priority <span>*</span></label>
            <select id="priority" name="priority" required>
                <option value="high" @selected(old('priority', $task?->priority ?? 'medium') === 'high')>High</option>
                <option value="medium" @selected(old('priority', $task?->priority ?? 'medium') === 'medium')>Medium</option>
                <option value="low" @selected(old('priority', $task?->priority ?? 'medium') === 'low')>Low</option>
            </select>
            @error('priority')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field-group form-span-2">
            <label for="task_title">Task title <span>*</span></label>
            <input id="task_title" name="task_title" type="text" value="{{ old('task_title', $task?->task_title) }}" maxlength="255" required placeholder="e.g. Submit the learning reflection">
            @error('task_title')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field-group form-span-2">
            <label for="description">Instructions <span class="optional">Optional</span></label>
            <textarea id="description" name="description" rows="7" maxlength="5000" placeholder="Explain what the member needs to complete and what a finished submission should contain.">{{ old('description', $task?->description) }}</textarea>
            @error('description')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field-group">
            <label for="deadline">Deadline <span class="optional">Optional</span></label>
            <input id="deadline" name="deadline" type="datetime-local" value="{{ old('deadline', $task?->deadline?->format('Y-m-d\\TH:i')) }}">
            @error('deadline')<span class="field-error">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="form-actions"><a class="button button-ghost" href="{{ route('admin.tasks.index') }}">Cancel</a><button class="button button-primary" type="submit">{{ $submitLabel }}</button></div>
</form>
