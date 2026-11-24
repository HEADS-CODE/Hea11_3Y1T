@extends('layouts.puihaha')
@section('title', 'Categories | Puihaha')
@section('content')
<div class="page-intro"><div><span class="eyebrow">Membership guide</span><h1>Member <em>categories.</em></h1><p>Explore the groups that shape each Puihaha learning journey.</p></div></div>
<section class="card-grid member-catalog-grid">@forelse($categories as $category)<article class="panel category-card"><div class="card-topline"><span class="category-symbol">✦</span><span class="status-badge status-active">{{ (int) $currentCategoryId === (int) $category->id ? 'Current category' : 'Available' }}</span></div><h2>{{ $category->name }}</h2><p>{{ $category->description ?: 'A membership group designed around a distinct learning journey.' }}</p>@if((int) $currentCategoryId === (int) $category->id)<span class="field-hint">You are currently enrolled in this category.</span>@else<a class="button button-primary" href="{{ route('member.category-change', ['category' => $category->id]) }}">Apply / Switch category <span>↗</span></a>@endif</article>@empty<div class="panel empty-state"><h3>No categories are available yet.</h3></div>@endforelse</section>
@endsection
