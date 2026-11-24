@extends('layouts.puihaha')
@section('title', 'Private Document Viewer | Puihaha')
@section('content')
<div class="page-intro compact-intro no-print"><div><span class="eyebrow">Private document viewer · {{ $member->membership_number }}</span><h1>Document <em>preview.</em></h1><p>{{ $document->original_name }} · {{ strtoupper($document->extension) }}</p></div><a class="button button-ghost" href="{{ route('members.documents.index', $member) }}">← Back</a></div>
<section class="panel document-viewer-panel">
    <div class="document-viewer-heading"><div><span class="eyebrow">Administrator-only record</span><h2>{{ $document->original_name }}</h2><p>{{ ucwords(str_replace('_', ' ', $document->document_type)) }} · Uploaded {{ $document->created_at?->format('M d, Y') }}</p></div></div>
    <div class="document-viewer-frame"><iframe src="{{ route('members.documents.view', [$member, $document]) }}" title="Private supporting document preview"></iframe></div>
    <p class="document-policy-reminder">Policy reminder: this supporting document is private, may be viewed only by authorized administrators, and must not be shared or downloaded outside the approved review process.</p>
</section>
@endsection
