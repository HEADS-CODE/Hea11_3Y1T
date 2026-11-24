<header class="platform-topbar portal-topbar {{ auth()->user()?->isMember() ? 'member-topbar' : 'admin-topbar' }}">
  <div class="portal-topbar-inner">
    <a class="platform-brand portal-brand" href="{{ auth()->user()?->isAdmin() ? route('admin.dashboard') : route('tasks.index') }}"><x-brand-logo variant="symbol" size="36" /><span><strong>Puihaha</strong><small>{{ auth()->user()?->isAdmin() ? 'Administrator portal' : 'Member portal' }}</small></span></a>
    <button class="portal-mobile-menu" type="button" data-mobile-nav-toggle aria-expanded="false" aria-controls="portal-main-nav"><span class="portal-mobile-menu-icon" aria-hidden="true">☰</span><span>Menu</span></button>
    <nav class="platform-nav portal-nav portal-main-nav" id="portal-main-nav" aria-label="Main navigation">
        @if(auth()->user()?->isAdmin())
            @php($pendingApprovalsCount = \App\Models\User::where('status', \App\Models\User::STATUS_PENDING)->count())
            <a class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <div class="portal-nav-dropdown {{ request()->routeIs('admin.approvals.*') || request()->routeIs('admin.requests.*') ? 'is-active' : '' }}" data-nav-dropdown>
                <button type="button" class="portal-nav-dropdown-toggle" data-nav-dropdown-toggle aria-expanded="false" aria-haspopup="true">Approvals @if($pendingApprovalsCount > 0)<span class="nav-count-pill">{{ $pendingApprovalsCount }}</span>@endif</button>
                <div class="portal-nav-dropdown-menu" data-nav-dropdown-menu hidden>
                    <a href="{{ route('admin.approvals.index') }}">Account approvals</a>
                    <a href="{{ route('admin.requests.index') }}">Member requests</a>
                </div>
            </div>
            <a class="{{ request()->routeIs('members.*') ? 'is-active' : '' }}" href="{{ route('members.index') }}">Members</a>
            <a class="{{ request()->routeIs('admin.transactions.*') ? 'is-active' : '' }}" href="{{ route('admin.transactions.index') }}">Transactions</a>
            <a class="{{ request()->routeIs('admin.tasks.*') ? 'is-active' : '' }}" href="{{ route('admin.tasks.index') }}">Tasks</a>
            <div class="portal-nav-dropdown {{ request()->routeIs('categories.*') || request()->routeIs('levels.*') ? 'is-active' : '' }}" data-nav-dropdown>
                <button type="button" class="portal-nav-dropdown-toggle" data-nav-dropdown-toggle aria-expanded="false" aria-haspopup="true">Membership</button>
                <div class="portal-nav-dropdown-menu" data-nav-dropdown-menu hidden>
                    <a href="{{ route('categories.index') }}">Categories</a>
                    <a href="{{ route('levels.index') }}">Levels</a>
                </div>
            </div>
            <a class="{{ request()->routeIs('admin.reports.*') ? 'is-active' : '' }}" href="{{ route('admin.reports.index') }}">Reports</a>
            @if(Route::has('admin.activity.index'))<a class="{{ request()->routeIs('admin.activity.*') ? 'is-active' : '' }}" href="{{ route('admin.activity.index') }}">Activity log</a>@endif
        @else
            <a class="{{ request()->routeIs('tasks.index') ? 'is-active' : '' }}" href="{{ route('tasks.index') }}">Home</a><a class="portal-insights-link js-task-scroll" href="{{ route('tasks.index') }}#insights">Insights</a><a class="{{ request()->routeIs('member.tasks') ? 'is-active' : '' }}" href="{{ route('member.tasks') }}">My Tasks</a><a class="{{ request()->routeIs('member.membership') ? 'is-active' : '' }}" href="{{ route('member.membership') }}">My Membership</a><a class="{{ request()->routeIs('member.categories') ? 'is-active' : '' }}" href="{{ route('member.categories') }}">Categories</a><a class="{{ request()->routeIs('member.levels') ? 'is-active' : '' }}" href="{{ route('member.levels') }}">Levels</a><a class="{{ request()->routeIs('member.transactions') ? 'is-active' : '' }}" href="{{ route('member.transactions') }}">History</a>
        @endif
    </nav>
    <div class="portal-actions">
        <div class="portal-notification-wrap"><button class="portal-icon-button" type="button" aria-label="Notifications" title="Notifications" data-notification-button aria-expanded="false"><img class="portal-control-icon icon-light" src="{{ asset('assets/icons/bell-light.svg') }}" alt=""><img class="portal-control-icon icon-dark" src="{{ asset('assets/icons/bell-dark.svg') }}" alt=""><span class="notification-badge" data-notification-count hidden>0</span></button><div class="portal-notification-panel" data-notification-panel hidden><div class="notification-panel-heading"><strong>Notifications</strong><button type="button" data-notification-read-all>Mark all read</button></div><div data-notification-list><p class="notification-empty">No notifications yet.</p></div></div></div>
        <div class="portal-profile-wrap">
        <button class="portal-profile-button" type="button" aria-label="Open profile menu" aria-expanded="false" data-profile-toggle><span class="portal-profile-avatar" aria-hidden="true"><img class="portal-control-icon icon-light" src="{{ asset('assets/icons/profile-light.svg') }}" alt=""><img class="portal-control-icon icon-dark" src="{{ asset('assets/icons/profile-dark.svg') }}" alt=""></span><span>Profile</span></button>
        <div class="portal-profile-menu" data-profile-menu hidden>
            @if(auth()->user()?->isAdmin())
                <div class="profile-menu-heading"><strong>{{ auth()->user()->adminProfile?->full_name ?: auth()->user()->name }}</strong><span>{{ auth()->user()->email }}</span><small>Administrator</small></div><a href="{{ route('admin.profile.edit') }}">Edit profile</a>
            @else
                <div class="profile-menu-heading"><strong>{{ auth()->user()->member?->full_name ?: auth()->user()->name }}</strong><span>{{ auth()->user()->member?->category?->name }} · {{ auth()->user()->member?->level?->name }}</span></div><a href="{{ route('member.requests.index') }}">My requests</a><a href="{{ route('member.profile-change') }}">Edit details</a><a href="{{ route('member.renew') }}">Renew</a><a href="{{ route('member.level-change') }}">Change level</a>@if(Route::has('member.category-change'))<a href="{{ route('member.category-change') }}">Change category</a>@endif
            @endif
            <button type="button" class="profile-signout" data-signout-trigger>Sign out</button>
        </div>
        </div>
        <button class="portal-theme-button theme-toggle" type="button" data-theme-toggle aria-pressed="false" title="Switch to dark mode"><span class="theme-switch-track" aria-hidden="true"><span class="theme-switch-thumb"><span class="theme-toggle-icon" data-theme-icon>☾</span></span></span><span class="theme-label" data-theme-label>Dark mode</span></button>
    </div>
  </div>
</header>
<div class="modal-backdrop" data-signout-modal hidden><div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="signout-title"><h2 id="signout-title">Sign out of Puihaha?</h2><p>Your current session will end.</p><div class="modal-actions"><button class="button button-ghost" type="button" data-signout-cancel>Cancel</button><form method="POST" action="{{ route('logout') }}">@csrf<button class="button button-primary" type="submit">Sign out</button></form></div></div></div>
