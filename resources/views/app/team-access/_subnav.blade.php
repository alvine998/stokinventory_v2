<nav class="md-subnav">
    @php
        $tabs = [
            ['team-access.approval-workflows', 'approval_workflow',  'fa-code-branch'],
            ['team-access.approval-requests',  'approval_requests',  'fa-inbox'],
            ['team-access.audit-log',          'audit_log',          'fa-shield-halved'],
            ['team-access.login-history',      'login_history',      'fa-right-to-bracket'],
            ['team-access.activity-log',       'activity_tracking',  'fa-chart-line'],
        ];
    @endphp
    @foreach ($tabs as [$route, $label, $icon])
        <a href="{{ route($route) }}" class="{{ request()->routeIs($route) ? 'active' : '' }}">
            <i class="fa-solid {{ $icon }}"></i> {{ __('messages.' . $label) }}
        </a>
    @endforeach
</nav>
