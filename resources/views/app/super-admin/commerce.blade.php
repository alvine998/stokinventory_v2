@extends('layouts.platform', ['title' => __('messages.super_admin_commerce'), 'heading' => __('messages.super_admin_commerce')])

@section('content')

{{-- ── PACKAGES ─────────────────────────────────── --}}
<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.platform_control') }}</p>
            <h2><i class="fa-solid fa-boxes-stacked"></i> {{ __('messages.current_packages') }}</h2>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
            <a class="secondary-button" href="{{ route('landing') }}" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> {{ __('messages.view_landing') }}</a>
            <a class="primary-button" href="#modal-create-package"><i class="fa-solid fa-plus"></i> {{ __('messages.create_package') }}</a>
        </div>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.package_name') }}</th>
                    <th>{{ __('messages.price') }}/{{ __('messages.month') }}</th>
                    <th>{{ __('messages.trial_days') }}</th>
                    <th>{{ __('messages.billing_periods') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($packages as $package)
            <tbody class="pkg-group">
                <tr>
                    <td>
                        <strong>{{ $package->name }}</strong>
                        @if ($package->is_featured) <span class="badge-tag">{{ __('messages.featured_package') }}</span> @endif
                        <br><small class="text-muted">{{ $package->tagline }}</small>
                    </td>
                    <td>Rp{{ number_format($package->discountedPrice(), 0, ',', '.') }}</td>
                    <td>{{ $package->trial_days }}d</td>
                    <td>
                        @if ($package->billing_periods)
                            <div class="period-badges" style="flex-wrap:wrap;gap:4px">
                                @foreach (collect($package->billing_periods)->sortBy('months') as $p)
                                    <span class="period-badge">{{ $p['months'] }}{{ __('messages.mo') }} -{{ $p['discount_percent'] }}%</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge-status {{ $package->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $package->is_active ? __('messages.active') : __('messages.inactive') }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                            <button type="button" class="icon-button" title="{{ __('messages.edit') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-pen':'fa-solid fa-chevron-up'">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('super-admin.packages.toggle', $package) }}">
                                @csrf @method('PATCH')
                                <button class="icon-button" title="{{ $package->is_active ? __('messages.deactivate') : __('messages.activate') }}">
                                    <i class="fa-solid fa-power-off"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('super-admin.packages.destroy', $package) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)" title="{{ __('messages.delete') }}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="6" style="padding:0">
                        <form method="POST" action="{{ route('super-admin.packages.update', $package) }}" class="edit-inline-form">
                            @csrf @method('PATCH')
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.package_name') }} <span class="req">*</span></span>
                                    <input name="name" value="{{ $package->name }}" required>
                                </label>
                                <label><span class="label-cap">{{ __('messages.tagline') }}</span>
                                    <input name="tagline" value="{{ $package->tagline }}">
                                </label>
                            </div>
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.price') }} (Rp/{{ __('messages.month') }}) <span class="req">*</span></span>
                                    <input name="price" type="number" min="0" step="0.01" value="{{ $package->price }}" required>
                                </label>
                                <label><span class="label-cap">{{ __('messages.default_discount') }} (%) <span class="req">*</span></span>
                                    <input name="discount_percent" type="number" min="0" max="95" value="{{ $package->discount_percent }}" required>
                                </label>
                            </div>
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.trial_days') }} <span class="req">*</span></span>
                                    <input name="trial_days" type="number" min="0" max="365" value="{{ $package->trial_days }}" required>
                                </label>
                            </div>
                            <fieldset class="period-fieldset">
                                <legend>{{ __('messages.billing_periods') }}</legend>
                                @php $existingPeriods = collect($package->billing_periods ?? [])->keyBy('months'); @endphp
                                @foreach ([6, 12, 24] as $mo)
                                <div class="period-row">
                                    <label class="period-row-label">{{ $mo }} {{ __('messages.months') }}</label>
                                    <div class="form-grid two" style="flex:1">
                                        <input name="period_months[]" type="number" value="{{ $mo }}" min="1" max="120">
                                        <input name="period_discount[]" type="number" value="{{ $existingPeriods->get($mo)['discount_percent'] ?? 0 }}" min="0" max="95">
                                    </div>
                                </div>
                                @endforeach
                            </fieldset>
                            <label><span class="label-cap">{{ __('messages.features_one_per_line') }}</span>
                                <textarea name="features" rows="4">{{ implode("\n", $package->features ?? []) }}</textarea>
                            </label>
                            <div class="form-grid two">
                                <label class="check-row"><input name="is_featured" type="checkbox" value="1" {{ $package->is_featured ? 'checked' : '' }}> {{ __('messages.featured_package') }}</label>
                                <label class="check-row"><input name="is_active" type="checkbox" value="1" {{ $package->is_active ? 'checked' : '' }}> {{ __('messages.active') }}</label>
                            </div>
                            <div class="modal-actions">
                                <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save_changes') }}</button>
                            </div>
                        </form>
                    </td>
                </tr>
            </tbody>
            @empty
            <tbody><tr><td colspan="6" class="empty-cell">{{ __('messages.no_packages') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
</section>

{{-- ── PROMO BANNERS ────────────────────────────── --}}
<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.platform_control') }}</p>
            <h2><i class="fa-solid fa-bullhorn"></i> {{ __('messages.current_banners') }}</h2>
        </div>
        <a class="primary-button" href="#modal-create-banner"><i class="fa-solid fa-plus"></i> {{ __('messages.create_promo_banner') }}</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:60px">{{ __('messages.banner_image') }}</th>
                    <th>{{ __('messages.title') }}</th>
                    <th>{{ __('messages.badge') }}</th>
                    <th>{{ __('messages.background') }}</th>
                    <th>{{ __('messages.starts_at') }} / {{ __('messages.ends_at') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($banners as $banner)
            <tbody class="banner-group">
                <tr>
                    <td>
                        @if ($banner->image)
                            <img src="{{ Storage::url($banner->image) }}" alt="{{ $banner->title }}" style="height:38px;width:60px;object-fit:cover;border-radius:6px;display:block">
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $banner->title }}</strong>
                        <br><small class="text-muted">{{ $banner->subtitle }}</small>
                    </td>
                    <td>{{ $banner->badge ?: '—' }}</td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:6px">
                            <span style="width:14px;height:14px;border-radius:3px;background:{{ $banner->background }};display:inline-block;border:1px solid rgba(0,0,0,.1)"></span>
                            <code style="font-size:12px">{{ $banner->background }}</code>
                        </span>
                    </td>
                    <td>
                        <small>{{ $banner->starts_at?->format('d M Y') ?? '—' }}<br>{{ $banner->ends_at?->format('d M Y') ?? '—' }}</small>
                    </td>
                    <td>
                        <span class="badge-status {{ $banner->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $banner->is_active ? __('messages.active') : __('messages.inactive') }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center">
                            <button type="button" class="icon-button" title="{{ __('messages.edit') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-pen':'fa-solid fa-chevron-up'">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('super-admin.promo-banners.toggle', $banner) }}">
                                @csrf @method('PATCH')
                                <button class="icon-button" title="{{ $banner->is_active ? __('messages.deactivate') : __('messages.activate') }}">
                                    <i class="fa-solid fa-power-off"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('super-admin.promo-banners.destroy', $banner) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)" title="{{ __('messages.delete') }}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="7" style="padding:0">
                        <form method="POST" action="{{ route('super-admin.promo-banners.update', $banner) }}" class="edit-inline-form" enctype="multipart/form-data">
                            @csrf @method('PATCH')
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.title') }} <span class="req">*</span></span>
                                    <input name="title" value="{{ $banner->title }}" required>
                                </label>
                                <label><span class="label-cap">{{ __('messages.badge') }}</span>
                                    <input name="badge" value="{{ $banner->badge }}">
                                </label>
                            </div>
                            <label><span class="label-cap">{{ __('messages.subtitle') }}</span>
                                <input name="subtitle" value="{{ $banner->subtitle }}">
                            </label>
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.button_label') }}</span>
                                    <input name="button_label" value="{{ $banner->button_label }}">
                                </label>
                                <label><span class="label-cap">{{ __('messages.button_url') }}</span>
                                    <input name="button_url" value="{{ $banner->button_url }}">
                                </label>
                            </div>
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.background') }} <span class="req">*</span></span>
                                    <input name="background" value="{{ $banner->background }}" required>
                                </label>
                                <label><span class="label-cap">{{ __('messages.banner_image') }}</span>
                                    @if ($banner->image)
                                        <img src="{{ Storage::url($banner->image) }}" style="max-height:36px;border-radius:5px;margin-bottom:4px;display:block">
                                    @endif
                                    <input name="image" type="file" accept="image/*">
                                </label>
                            </div>
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.starts_at') }}</span>
                                    <input name="starts_at" type="date" value="{{ $banner->starts_at?->toDateString() }}">
                                </label>
                                <label><span class="label-cap">{{ __('messages.ends_at') }}</span>
                                    <input name="ends_at" type="date" value="{{ $banner->ends_at?->toDateString() }}">
                                </label>
                            </div>
                            <div class="form-row-spread">
                                <label class="check-row"><input name="is_active" type="checkbox" value="1" {{ $banner->is_active ? 'checked' : '' }}> {{ __('messages.active') }}</label>
                                <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save_changes') }}</button>
                            </div>
                        </form>
                    </td>
                </tr>
            </tbody>
            @empty
            <tbody><tr><td colspan="7" class="empty-cell">{{ __('messages.no_banners') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
</section>

{{-- ── CREATE PACKAGE MODAL ─────────────────────── --}}
<div class="modal-overlay" id="modal-create-package" role="dialog" aria-modal="true" aria-labelledby="modal-create-package-title">
    <div class="modal-card" style="max-width:660px">
        <header>
            <h3 id="modal-create-package-title"><i class="fa-solid fa-boxes-stacked"></i> {{ __('messages.create_package') }}</h3>
            <a href="#" class="icon-button" aria-label="{{ __('messages.close') }}"><i class="fa-solid fa-xmark"></i></a>
        </header>
        <form method="POST" action="{{ route('super-admin.packages.store') }}" class="form-grid">
            @csrf
            <input name="name" placeholder="{{ __('messages.package_name') }}" required>
            <input name="tagline" placeholder="{{ __('messages.tagline') }}">
            <div class="form-grid two">
                <label>{{ __('messages.price') }} (Rp/{{ __('messages.month') }})<input name="price" type="number" min="0" step="0.01" placeholder="{{ __('messages.price') }}" required></label>
                <label>{{ __('messages.default_discount') }} (%)<input name="discount_percent" type="number" min="0" max="95" value="0" required></label>
            </div>
            <input name="trial_days" type="number" min="0" max="365" value="30" placeholder="{{ __('messages.trial_days') }}" required>
            <fieldset class="period-fieldset">
                <legend>{{ __('messages.billing_periods') }}</legend>
                @foreach ([6, 12, 24] as $mo)
                <div class="period-row">
                    <label class="period-row-label">{{ $mo }} {{ __('messages.months') }}</label>
                    <div class="form-grid two" style="flex:1">
                        <input name="period_months[]" type="number" value="{{ $mo }}" min="1" max="120">
                        <input name="period_discount[]" type="number" value="{{ $mo == 6 ? 10 : ($mo == 12 ? 20 : 35) }}" min="0" max="95">
                    </div>
                </div>
                @endforeach
                <p class="field-hint">{{ __('messages.billing_periods_hint') }}</p>
            </fieldset>
            <textarea name="features" placeholder="{{ __('messages.features_one_per_line') }}"></textarea>
            <div class="form-grid two">
                <label class="check-row"><input name="is_featured" type="checkbox" value="1"> {{ __('messages.featured_package') }}</label>
                <label class="check-row"><input name="is_active" type="checkbox" value="1" checked> {{ __('messages.active') }}</label>
            </div>
            <div class="modal-actions">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.add_new') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- ── CREATE BANNER MODAL ──────────────────────── --}}
<div class="modal-overlay" id="modal-create-banner" role="dialog" aria-modal="true" aria-labelledby="modal-create-banner-title">
    <div class="modal-card">
        <header>
            <h3 id="modal-create-banner-title"><i class="fa-solid fa-bullhorn"></i> {{ __('messages.create_promo_banner') }}</h3>
            <a href="#" class="icon-button" aria-label="{{ __('messages.close') }}"><i class="fa-solid fa-xmark"></i></a>
        </header>
        <form method="POST" action="{{ route('super-admin.promo-banners.store') }}" class="form-grid" enctype="multipart/form-data">
            @csrf
            <input name="badge" placeholder="{{ __('messages.badge') }}">
            <input name="title" placeholder="{{ __('messages.title') }}" required>
            <input name="subtitle" placeholder="{{ __('messages.subtitle') }}">
            <div class="form-grid two">
                <input name="button_label" placeholder="{{ __('messages.button_label') }}">
                <input name="button_url" placeholder="{{ __('messages.button_url') }}">
            </div>
            <input name="background" value="#0f766e" placeholder="{{ __('messages.background') }}" required>
            <label><span class="label-cap">{{ __('messages.banner_image') }} <small class="field-hint">{{ __('messages.banner_image_hint') }}</small></span>
                <input name="image" type="file" accept="image/*">
            </label>
            <div class="form-grid two">
                <input name="starts_at" type="date">
                <input name="ends_at" type="date">
            </div>
            <label class="check-row"><input name="is_active" type="checkbox" value="1" checked> {{ __('messages.active') }}</label>
            <div class="modal-actions">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button"><i class="fa-solid fa-bullhorn"></i> {{ __('messages.publish_banner') }}</button>
            </div>
        </form>
    </div>
</div>

@endsection
