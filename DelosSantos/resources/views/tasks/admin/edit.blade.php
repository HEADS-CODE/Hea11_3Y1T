@extends('layouts.puihaha')

@section('title', 'Edit Learning Task | Puihaha')

@section('content')
<div class="page-intro">
    <div><span class="eyebrow">Administrator workspace</span><h1>Edit the <em>assignment.</em></h1><p>Keep the member’s instructions and deadline accurate as the learning plan changes.</p></div>
    <a class="button button-ghost" href="{{ route('admin.tasks.index') }}">← Task register</a>
</div>
<section class="panel form-panel admin-task-form-panel">
    <div class="panel-heading"><div><span class="eyebrow">Existing assignment</span><h2>{{ $task->task_title }}</h2></div></div>
    @include('tasks.admin._form', ['formAction' => route('admin.tasks.update', $task), 'formMethod' => 'PUT', 'submitLabel' => 'Save changes'])
</section>
@endsection
