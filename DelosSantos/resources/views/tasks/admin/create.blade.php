@extends('layouts.puihaha')

@section('title', 'Assign Learning Task | Puihaha')

@section('content')
<div class="page-intro">
    <div><span class="eyebrow">Administrator workspace</span><h1>Assign a <em>learning task.</em></h1><p>Give one active member—or every active member—a clear instruction and, when needed, a deadline.</p></div>
    <a class="button button-ghost" href="{{ route('admin.tasks.index') }}">← Task register</a>
</div>
<section class="panel form-panel admin-task-form-panel">
    <div class="panel-heading"><div><span class="eyebrow">New assignment</span><h2>Task details</h2></div></div>
    @include('tasks.admin._form', ['task' => null, 'formAction' => route('admin.tasks.store'), 'formMethod' => 'POST', 'submitLabel' => 'Assign task'])
</section>
@endsection
