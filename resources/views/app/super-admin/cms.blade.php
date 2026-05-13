@extends('layouts.platform', ['title' => __('messages.cms_management'), 'heading' => __('messages.cms_management')])

@section('content')
<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.platform_control') }}</p>
            <h2><i class="fa-solid fa-file-lines"></i> {{ __('messages.cms_management') }}</h2>
        </div>
        <a href="#modal-add-cms" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.add_new') }}</a>
    </div>

    @include('partials.errors')

    <div class="cms-list">
        @forelse ($pages as $page)
        <details class="cms-entry">
            <summary>
                <div class="cms-entry-meta">
                    <span class="badge-tag">{{ $page->section }}</span>
                    <strong>{{ $page->title }}</strong>
                    <code class="slug-pill">{{ $page->slug }}</code>
                </div>
                <span class="badge-status {{ $page->is_published ? 'badge-active' : 'badge-inactive' }}">
                    {{ $page->is_published ? __('messages.published') : __('messages.draft') }}
                </span>
            </summary>
            <form method="POST" action="{{ route('super-admin.cms.update', $page) }}" class="cms-edit-form">
                @csrf @method('PATCH')
                <div class="form-grid two">
                    <label><span class="label-cap">{{ __('messages.title') }} <span class="req">*</span></span>
                        <input name="title" value="{{ $page->title }}" required>
                    </label>
                    <label><span class="label-cap">{{ __('messages.section') }} <span class="req">*</span></span>
                        <input name="section" value="{{ $page->section }}" required>
                    </label>
                </div>
                <label>{{ __('messages.content') }}
                    <textarea name="body" rows="6">{{ $page->body }}</textarea>
                </label>
                <div class="form-row-spread">
                    <label class="check-row"><input name="is_published" type="checkbox" value="1" @checked($page->is_published)> {{ __('messages.published') }}</label>
                    <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save_changes') }}</button>
                </div>
            </form>
        </details>
        @empty
            <p class="empty-cell">{{ __('messages.no_cms_pages') }}</p>
        @endforelse
    </div>
</section>

{{-- Add CMS Page Modal --}}
<div class="modal-overlay" id="modal-add-cms" role="dialog" aria-modal="true" aria-labelledby="modal-add-cms-title">
    <div class="modal-card">
        <header>
            <h3 id="modal-add-cms-title"><i class="fa-solid fa-file-circle-plus"></i> {{ __('messages.add_cms_page') }}</h3>
            <a href="#" class="icon-button" aria-label="{{ __('messages.close') }}"><i class="fa-solid fa-xmark"></i></a>
        </header>
        <form method="POST" action="{{ route('super-admin.cms.store') }}" class="form-grid">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.title') }} <span class="req">*</span></span>
                    <input name="title" value="{{ old('title') }}" placeholder="{{ __('messages.title') }}" required>
                </label>
                <label><span class="label-cap">{{ __('messages.section') }} <span class="req">*</span></span>
                    <input name="section" value="{{ old('section', 'landing') }}" placeholder="landing, about…" required>
                </label>
            </div>
            <label>{{ __('messages.content') }}
                <textarea name="body" rows="5" placeholder="{{ __('messages.content') }}">{{ old('body') }}</textarea>
            </label>
            <label class="check-row"><input name="is_published" type="checkbox" value="1" checked> {{ __('messages.published') }}</label>
            <div class="modal-actions">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

