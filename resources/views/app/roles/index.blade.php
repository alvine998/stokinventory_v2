@extends('layouts.app', ['title' => __('messages.roles'), 'heading' => __('messages.roles')])

@section('content')
<section class="role-hero panel">
    <div>
        <p class="eyebrow">{{ __('messages.access_control') }}</p>
        <h2><i class="fa-solid fa-user-shield"></i> {{ __('messages.role_management_title') }}</h2>
        <p>{{ __('messages.role_management_body') }}</p>
    </div>
    <div class="role-hero-stats">
        <span>{{ $roles->count() }}</span>
        <small>{{ __('messages.active_roles') }}</small>
    </div>
</section>

@include('partials.errors')

<section class="role-layout">
    <form method="POST" action="{{ route('roles.store') }}" class="role-builder">
        @csrf
        <div class="section-head">
            <div>
                <p class="eyebrow">{{ __('messages.create_role') }}</p>
                <h2>{{ __('messages.new_role') }}</h2>
            </div>
            <button class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.save_role') }}</button>
        </div>

        <label>{{ __('messages.role_name') }}<input name="name" placeholder="{{ __('messages.role_name_placeholder') }}" required></label>

        <div class="permission-board">
            @foreach ($permissionGroups as $group => $permissions)
                <article class="permission-group">
                    <header>
                        <i class="fa-solid {{ ['workspace' => 'fa-chart-line', 'people' => 'fa-users-gear', 'inventory' => 'fa-boxes-stacked', 'inventory_ops' => 'fa-sliders', 'master_data' => 'fa-database', 'purchasing' => 'fa-cart-shopping', 'sales' => 'fa-bag-shopping', 'finance' => 'fa-landmark', 'reporting' => 'fa-chart-bar', 'team_access' => 'fa-user-lock'][$group] }}"></i>
                        <div>
                            <h3>{{ __('messages.permission_group_' . $group) }}</h3>
                            <p>{{ __('messages.permission_group_' . $group . '_hint') }}</p>
                        </div>
                    </header>
                    <div class="permission-options">
                        @foreach ($permissions as $permission)
                            <label class="permission-option">
                                <input type="checkbox" name="permissions[]" value="{{ $permission }}" placeholder="{{ $permission }}">
                                <span>
                                    <strong>{{ __('messages.permission_' . str_replace('.', '_', $permission)) }}</strong>
                                    <small>{{ $permission }}</small>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    </form>

    <aside class="role-list">
        <div class="section-head">
            <div>
                <p class="eyebrow">{{ __('messages.current_roles') }}</p>
                <h2>{{ __('messages.role_library') }}</h2>
            </div>
        </div>

        @forelse ($roles as $role)
            <article class="role-card">
                <div class="role-card-head">
                    <span class="role-icon"><i class="fa-solid fa-id-badge"></i></span>
                    <div>
                        <h3>{{ $role->name }}</h3>
                        <p>{{ $role->slug }}</p>
                    </div>
                </div>
                <div class="permission-chip-list">
                    @forelse ($role->permissions ?? [] as $permission)
                        <span>{{ __('messages.permission_' . str_replace('.', '_', $permission)) }}</span>
                    @empty
                        <span>{{ __('messages.no_permissions') }}</span>
                    @endforelse
                </div>
            </article>
        @empty
            <div class="empty-state"><i class="fa-solid fa-user-shield"></i><p>{{ __('messages.no_data_yet') }}</p></div>
        @endforelse
    </aside>
</section>
@endsection
