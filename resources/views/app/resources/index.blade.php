@php
    $labels = [
        'users' => ['icon' => 'fa-users', 'route' => 'users.index'],
        'roles' => ['icon' => 'fa-user-shield', 'route' => 'roles.index'],
        'user_roles' => ['icon' => 'fa-id-badge', 'route' => 'user-roles.index'],
        'stock_opname' => ['icon' => 'fa-clipboard-check', 'route' => 'stock-opname.index'],
        'stores' => ['icon' => 'fa-store', 'route' => 'stores.index'],
        'warehouses' => ['icon' => 'fa-warehouse', 'route' => 'warehouses.index'],
        'products' => ['icon' => 'fa-boxes-stacked', 'route' => 'products.index'],
        'stock_movements' => ['icon' => 'fa-right-left', 'route' => 'stock-movements.index'],
        'packages' => ['icon' => 'fa-cubes', 'route' => 'packages.index'],
    ];
    $hasImageColumn = in_array($resource ?? '', ['products', 'stock_movements', 'stock_opname', 'users'], true);
    $modalResources = ['stock_movements', 'stock_opname', 'packages', 'users'];
    $pageCreateRoutes = [
        'stores' => 'stores.create',
        'warehouses' => 'warehouses.create',
        'products' => 'products.create',
    ];
@endphp
@extends('layouts.app', ['title' => __('messages.' . $resource), 'heading' => __('messages.' . $resource)])

@section('content')
<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.module') }}</p>
            <h2><i class="fa-solid {{ $labels[$resource]['icon'] }}"></i> {{ __('messages.' . $resource) }}</h2>
        </div>
        <div class="topbar-actions">
            @if (isset($pageCreateRoutes[$resource]))
                <a class="primary-button" href="{{ route($pageCreateRoutes[$resource]) }}"><i class="fa-solid fa-plus"></i> {{ __('messages.add_new') }}</a>
            @endif
            @if (in_array($resource, $modalResources, true))
                <a class="primary-button" href="#create-record-modal"><i class="fa-solid fa-plus"></i> {{ __('messages.add_new') }}</a>
            @endif
            @if (in_array($resource, ['stock_movements', 'stock_opname'], true))
                <button class="secondary-button" onclick="window.print()"><i class="fa-solid fa-print"></i> {{ __('messages.print_receipt') }}</button>
            @endif
        </div>
    </div>

    @include('partials.errors')

    @if (! in_array($resource, array_merge(array_keys($pageCreateRoutes), $modalResources), true))
        <form method="POST" action="{{ route($labels[$resource]['route'] === 'stock-opname.index' ? 'stock-opname.store' : str_replace('.index', '.store', $labels[$resource]['route'])) }}" class="quick-form">
            @csrf
            @if ($resource === 'roles')
                <input name="name" placeholder="{{ __('messages.role_name') }}" required>
                <div class="permission-grid">
                    @foreach ($permissions as $permission)
                        <label class="check-row"><input type="checkbox" name="permissions[]" value="{{ $permission }}" placeholder="{{ $permission }}"> {{ $permission }}</label>
                    @endforeach
                </div>
            @elseif ($resource === 'user_roles')
                <select name="user_id" required><option value="">{{ __('messages.select_user') }}</option>@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select>
                <select name="role_id" required><option value="">{{ __('messages.select_role') }}</option>@foreach ($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select>
            @endif
            <button class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.add_new') }}</button>
        </form>
    @endif

    @if ($resource === 'stock_movements')
        <div id="create-record-modal" class="modal-overlay">
            <form method="POST" action="{{ route('stock-movements.store') }}" class="modal-card" enctype="multipart/form-data">
                @csrf
                <header><h3>{{ __('messages.create_stock_movement') }}</h3><a href="#" class="icon-button"><i class="fa-solid fa-xmark"></i></a></header>
                <div class="form-grid two">
                    <select name="product_id" required><option value="">{{ __('messages.select_product') }}</option>@foreach ($products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach</select>
                    <select name="warehouse_id"><option value="">{{ __('messages.select_warehouse') }}</option>@foreach ($warehouses as $warehouse)<option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>@endforeach</select>
                    <select name="type" required><option value="in">In</option><option value="out">Out</option><option value="transfer">Transfer</option></select>
                    <input name="quantity" type="number" min="1" placeholder="{{ __('messages.quantity') }}" required>
                    <input name="reference_no" placeholder="{{ __('messages.reference_no') }}" required>
                    <input name="notes" placeholder="{{ __('messages.notes') }}">
                    <label class="file-input">{{ __('messages.evidence_image') }}<input name="evidence_image" type="file" accept="image/*" placeholder="{{ __('messages.evidence_image') }}"></label>
                </div>
                <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}</button>
            </form>
        </div>
    @elseif ($resource === 'stock_opname')
        <div id="create-record-modal" class="modal-overlay">
            <form method="POST" action="{{ route('stock-opname.store') }}" class="modal-card" enctype="multipart/form-data">
                @csrf
                <header><h3>{{ __('messages.create_stock_opname') }}</h3><a href="#" class="icon-button"><i class="fa-solid fa-xmark"></i></a></header>
                <div class="form-grid two">
                    <select name="warehouse_id"><option value="">{{ __('messages.select_warehouse') }}</option>@foreach ($warehouses as $warehouse)<option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>@endforeach</select>
                    <input name="reference_no" placeholder="{{ __('messages.reference_no') }}" required>
                    <input name="scheduled_at" type="date" placeholder="{{ __('messages.scheduled_at') }}">
                    <select name="status"><option value="draft">{{ __('messages.draft') }}</option><option value="in_progress">{{ __('messages.in_progress') }}</option><option value="completed">{{ __('messages.completed') }}</option></select>
                    <input name="notes" placeholder="{{ __('messages.notes') }}">
                    <label class="file-input">{{ __('messages.evidence_image') }}<input name="evidence_image" type="file" accept="image/*" placeholder="{{ __('messages.evidence_image') }}"></label>
                </div>
                <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}</button>
            </form>
        </div>
    @elseif ($resource === 'packages')
        <div id="create-record-modal" class="modal-overlay">
            <form method="POST" action="{{ route('packages.store') }}" class="modal-card">
                @csrf
                <header><h3>{{ __('messages.create_package') }}</h3><a href="#" class="icon-button"><i class="fa-solid fa-xmark"></i></a></header>
                <div class="form-grid two">
                    <input name="name" placeholder="{{ __('messages.package_name') }}" required>
                    <input name="code" placeholder="{{ __('messages.code') }}">
                    <input name="price" type="number" min="0" step="0.01" placeholder="{{ __('messages.price') }}" required>
                    <input name="description" placeholder="{{ __('messages.description') }}">
                    <input type="hidden" name="is_active" value="0">
                    <label class="check-row"><input type="checkbox" name="is_active" value="1" checked placeholder="{{ __('messages.active') }}"> {{ __('messages.active') }}</label>
                </div>
                <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}</button>
            </form>
        </div>
    @elseif ($resource === 'users')
        <div id="create-record-modal" class="modal-overlay">
            <form method="POST" action="{{ route('users.store') }}" class="modal-card" enctype="multipart/form-data">
                @csrf
                <header><h3>{{ __('messages.add_user') }}</h3><a href="#" class="icon-button"><i class="fa-solid fa-xmark"></i></a></header>
                <div class="form-grid two">
                    <input name="name" placeholder="{{ __('messages.name') }}" required>
                    <input name="email" type="email" placeholder="{{ __('messages.email') }}" required>
                    <input name="password" type="password" placeholder="{{ __('messages.password') }}" required>
                    <select name="platform_role">
                        <option value="customer">{{ __('messages.customer') }}</option>
                        <option value="staff">{{ __('messages.staff') }}</option>
                        <option value="manager">{{ __('messages.manager') }}</option>
                    </select>
                    <label class="file-input">{{ __('messages.photo') }}<input name="photo_path" type="file" accept="image/*" placeholder="{{ __('messages.photo') }}"></label>
                </div>
                <div class="form-grid">
                    <fieldset>
                        <legend>{{ __('messages.user_roles') }}</legend>
                        @foreach ($roles as $role)
                            <label class="check-row"><input type="checkbox" name="roles[]" value="{{ $role->id }}" placeholder="{{ $role->name }}"> {{ $role->name }}</label>
                        @endforeach
                    </fieldset>
                </div>
                <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}</button>
            </form>
        </div>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.name') }}</th>
                    @if ($hasImageColumn)
                        <th>{{ __('messages.image') }}</th>
                    @endif
                    <th>{{ __('messages.details') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $item->name ?? $item->reference_no ?? $item->email }}</td>
                    @if ($hasImageColumn)
                        <td>
                            @php
                                $imagePath = $resource === 'products' ? ($item->photo_path ?? null) : ($resource === 'users' ? ($item->photo_path ?? null) : ($item->evidence_image_path ?? null));
                            @endphp
                            @if ($imagePath)
                                <a href="{{ asset('storage/' . $imagePath) }}" target="_blank"><img class="table-thumb" src="{{ asset('storage/' . $imagePath) }}" alt="{{ __('messages.image') }}"></a>
                            @else
                                <span class="muted">-</span>
                            @endif
                        </td>
                    @endif
                    <td>
                        @if ($resource === 'stock_movements')
                            {{ optional($products->firstWhere('id', $item->product_id))->name ?? '-' }} / {{ $item->type }}
                        @elseif ($resource === 'stock_opname')
                            {{ optional($warehouses->firstWhere('id', $item->warehouse_id))->name ?? __('messages.select_warehouse') }}
                        @elseif ($resource === 'warehouses')
                            {{ optional($stores->firstWhere('id', $item->store_id))->name ?? '-' }}
                        @elseif ($resource === 'users')
                            {{ $item->roles->pluck('name')->join(', ') ?: __('messages.no_roles') }}
                        @elseif ($resource === 'user_roles')
                            {{ $item->roles->pluck('name')->join(', ') ?: '-' }}
                        @elseif ($resource === 'roles')
                            {{ collect($item->permissions ?? [])->join(', ') }}
                        @else
                            {{ $item->code ?? $item->sku ?? $item->email ?? $item->type ?? '-' }}
                        @endif
                    </td>
                    <td><span class="status">{{ $item->status ?? ($item->is_active ?? true ? __('messages.active') : __('messages.inactive')) }}</span></td>
                    <td>
                        @if ($resource === 'products')
                            <a class="icon-button" href="{{ route('products.edit', $item) }}" title="{{ __('messages.edit_product') }}"><i class="fa-solid fa-pen"></i></a>
                        @elseif ($resource === 'stores')
                            <a class="icon-button" href="{{ route('stores.edit', $item) }}" title="{{ __('messages.edit_store') }}"><i class="fa-solid fa-pen"></i></a>
                        @elseif ($resource === 'warehouses')
                            <a class="icon-button" href="{{ route('warehouses.edit', $item) }}" title="{{ __('messages.edit_warehouse') }}"><i class="fa-solid fa-pen"></i></a>
                        @elseif (in_array($resource, $modalResources, true))
                            <a class="icon-button" href="#edit-record-modal-{{ $item->id }}" title="{{ __('messages.edit') }}"><i class="fa-solid fa-pen"></i></a>
                        @else
                            <button class="icon-button"><i class="fa-solid fa-pen"></i></button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $hasImageColumn ? 5 : 4 }}"><div class="empty-state"><i class="fa-solid fa-folder-open"></i><p>{{ __('messages.no_data_yet') }}</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($resource === 'stock_movements')
        @foreach ($items as $item)
            <div id="edit-record-modal-{{ $item->id }}" class="modal-overlay">
                <form method="POST" action="{{ route('stock-movements.update', $item) }}" class="modal-card" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <header><h3>{{ __('messages.edit_stock_movement') }}</h3><a href="#" class="icon-button"><i class="fa-solid fa-xmark"></i></a></header>
                    <div class="form-grid two">
                        <select name="product_id" required><option value="">{{ __('messages.select_product') }}</option>@foreach ($products as $product)<option value="{{ $product->id }}" @selected($item->product_id === $product->id)>{{ $product->name }}</option>@endforeach</select>
                        <select name="warehouse_id"><option value="">{{ __('messages.select_warehouse') }}</option>@foreach ($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected($item->warehouse_id === $warehouse->id)>{{ $warehouse->name }}</option>@endforeach</select>
                        <select name="type" required><option value="in" @selected($item->type === 'in')>In</option><option value="out" @selected($item->type === 'out')>Out</option><option value="transfer" @selected($item->type === 'transfer')>Transfer</option></select>
                        <input name="quantity" type="number" min="1" value="{{ $item->quantity }}" placeholder="{{ __('messages.quantity') }}" required>
                        <input name="reference_no" value="{{ $item->reference_no }}" placeholder="{{ __('messages.reference_no') }}" required>
                        <input name="notes" value="{{ $item->notes }}" placeholder="{{ __('messages.notes') }}">
                        <label class="file-input">{{ __('messages.evidence_image') }}<input name="evidence_image" type="file" accept="image/*" placeholder="{{ __('messages.evidence_image') }}"></label>
                    </div>
                    <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}</button>
                </form>
            </div>
        @endforeach
    @elseif ($resource === 'stock_opname')
        @foreach ($items as $item)
            <div id="edit-record-modal-{{ $item->id }}" class="modal-overlay">
                <form method="POST" action="{{ route('stock-opname.update', $item) }}" class="modal-card" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <header><h3>{{ __('messages.edit_stock_opname') }}</h3><a href="#" class="icon-button"><i class="fa-solid fa-xmark"></i></a></header>
                    <div class="form-grid two">
                        <select name="warehouse_id"><option value="">{{ __('messages.select_warehouse') }}</option>@foreach ($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected($item->warehouse_id === $warehouse->id)>{{ $warehouse->name }}</option>@endforeach</select>
                        <input name="reference_no" value="{{ $item->reference_no }}" placeholder="{{ __('messages.reference_no') }}" required>
                        <input name="scheduled_at" type="date" value="{{ optional($item->scheduled_at)->format('Y-m-d') }}" placeholder="{{ __('messages.scheduled_at') }}">
                        <select name="status"><option value="draft" @selected($item->status === 'draft')>{{ __('messages.draft') }}</option><option value="in_progress" @selected($item->status === 'in_progress')>{{ __('messages.in_progress') }}</option><option value="completed" @selected($item->status === 'completed')>{{ __('messages.completed') }}</option></select>
                        <input name="notes" value="{{ $item->notes }}" placeholder="{{ __('messages.notes') }}">
                        <label class="file-input">{{ __('messages.evidence_image') }}<input name="evidence_image" type="file" accept="image/*" placeholder="{{ __('messages.evidence_image') }}"></label>
                    </div>
                    <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}</button>
                </form>
            </div>
        @endforeach
    @elseif ($resource === 'packages')
        @foreach ($items as $item)
            <div id="edit-record-modal-{{ $item->id }}" class="modal-overlay">
                <form method="POST" action="{{ route('packages.update', $item) }}" class="modal-card">
                    @csrf
                    @method('PUT')
                    <header><h3>{{ __('messages.edit_package') }}</h3><a href="#" class="icon-button"><i class="fa-solid fa-xmark"></i></a></header>
                    <div class="form-grid two">
                        <input name="name" value="{{ $item->name }}" placeholder="{{ __('messages.package_name') }}" required>
                        <input name="code" value="{{ $item->code }}" placeholder="{{ __('messages.code') }}">
                        <input name="price" type="number" min="0" step="0.01" value="{{ $item->price }}" placeholder="{{ __('messages.price') }}" required>
                        <input name="description" value="{{ $item->description }}" placeholder="{{ __('messages.description') }}">
                        <input type="hidden" name="is_active" value="0">
                        <label class="check-row"><input type="checkbox" name="is_active" value="1" @checked($item->is_active) placeholder="{{ __('messages.active') }}"> {{ __('messages.active') }}</label>
                    </div>
                    <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}</button>
                </form>
            </div>
        @endforeach
    @elseif ($resource === 'users')
        @foreach ($items as $item)
            <div id="edit-record-modal-{{ $item->id }}" class="modal-overlay">
                <form method="POST" action="{{ route('users.update', $item) }}" class="modal-card" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <header><h3>{{ __('messages.edit_user') }}</h3><a href="#" class="icon-button"><i class="fa-solid fa-xmark"></i></a></header>
                    <div class="form-grid two">
                        <input name="name" value="{{ $item->name }}" placeholder="{{ __('messages.name') }}" required>
                        <input name="email" type="email" value="{{ $item->email }}" placeholder="{{ __('messages.email') }}" required>
                        <input name="password" type="password" placeholder="{{ __('messages.password') }}" autocomplete="new-password">
                        <select name="platform_role">
                            <option value="customer" @selected($item->platform_role === 'customer')>{{ __('messages.customer') }}</option>
                            <option value="staff" @selected($item->platform_role === 'staff')>{{ __('messages.staff') }}</option>
                            <option value="manager" @selected($item->platform_role === 'manager')>{{ __('messages.manager') }}</option>
                        </select>
                        <label class="file-input">{{ __('messages.photo') }}<input name="photo_path" type="file" accept="image/*" placeholder="{{ __('messages.photo') }}"></label>
                    </div>
                    <div class="form-grid">
                        <fieldset>
                            <legend>{{ __('messages.user_roles') }}</legend>
                            @foreach ($roles as $role)
                                <label class="check-row"><input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked($item->roles->contains($role->id)) placeholder="{{ $role->name }}"> {{ $role->name }}</label>
                            @endforeach
                        </fieldset>
                    </div>
                    <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}</button>
                </form>
            </div>
        @endforeach
    @endif
</section>
@endsection
