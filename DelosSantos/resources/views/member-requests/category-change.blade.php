@extends('layouts.puihaha')
@section('title', 'Change Category | Puihaha')
@section('content')
<div class="page-intro compact-intro"><div><span class="eyebrow">Membership request</span><h1>Change your <em>category.</em></h1><p>Your current category is <strong>{{ $member->category?->name }}</strong>. Category changes require supporting eligibility documents and administrator approval.</p></div></div>
<section class="panel form-panel narrow-panel"><div class="panel-heading"><div><span class="eyebrow">Online path</span><h2>Request a category change</h2></div></div>
@include('components.validation-summary')
<form method="POST" action="{{ route('member.category-change.store') }}" enctype="multipart/form-data" class="form-stack" data-live-validation>@csrf
    <div class="form-field"><label for="membership_category_id">New category <span>*</span></label><select id="membership_category_id" name="membership_category_id" required><option value="">Choose a different category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((int) ($selectedCategoryId ?? old('membership_category_id')) === (int) $category->id)>{{ $category->name }}</option>@endforeach</select>@error('membership_category_id')<span class="field-error">{{ $message }}</span>@enderror</div>
    <div class="form-field"><label for="documents">Eligibility document <span>*</span></label><input id="documents" name="documents[]" type="file" accept=".pdf,.jpg,.jpeg,.png" required>@error('documents')<span class="field-error">{{ $message }}</span>@enderror<small class="field-hint">PDF, JPG, or PNG up to 5 MB. For example, submit school, senior-citizen, or corporate eligibility evidence as applicable.</small></div>
    <label class="consent-check"><input type="checkbox" name="privacy_consent" value="1" required> I consent to the secure processing of this document for this request.</label>@error('privacy_consent')<span class="field-error">{{ $message }}</span>@enderror
    <div class="form-actions"><a class="button button-ghost" href="{{ route('member.transactions') }}">Cancel</a><button class="button button-primary" type="submit">Send request <span>↗</span></button></div>
</form></section>
@endsection
