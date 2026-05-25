<nav class="redmine-tabs" aria-label="Project tabs">
    <a class="redmine-tab {{ $activeTab === 'overview' ? 'active' : '' }}" href="{{ $tabs['overview'] }}">Overview</a>
    <a class="redmine-tab {{ $activeTab === 'activity' ? 'active' : '' }}" href="{{ $tabs['activity'] }}">Activity</a>
    <a class="redmine-tab {{ $activeTab === 'issues' ? 'active' : '' }}" href="{{ $tabs['issues'] }}">Issues</a>
    <a class="redmine-tab {{ $activeTab === 'dashboard' ? 'active' : '' }}" href="{{ $tabs['dashboard'] }}">Dashboard</a>
    <a class="redmine-tab {{ $activeTab === 'gantt' ? 'active' : '' }}" href="{{ $tabs['gantt'] }}">Gantt</a>
</nav>
