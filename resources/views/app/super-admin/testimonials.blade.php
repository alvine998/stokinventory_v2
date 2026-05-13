@extends('layouts.platform', ['title' => __('messages.testimonials'), 'heading' => __('messages.testimonials')])

@section('content')
<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.platform_control') }}</p>
            <h2><i class="fa-solid fa-quote-left"></i> {{ __('messages.manage_testimonials') }}</h2>
        </div>
        <a class="secondary-button" href="{{ route('landing') }}" target="_blank">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> {{ __('messages.view_landing') }}
        </a>
    </div>

    @include('partials.errors')

    <form method="POST" action="{{ route('super-admin.testimonials.store') }}" enctype="multipart/form-data" class="admin-form wide">
        @csrf
        <h3><i class="fa-solid fa-plus"></i> {{ __('messages.add_testimonial') }}</h3>
        <div class="form-grid two">
            <label>{{ __('messages.name') }}<input name="name" placeholder="{{ __('messages.full_name') }}" required></label>
            <label>{{ __('messages.role') }}<input name="role" placeholder="{{ __('messages.job_title') }}"></label>
            <label>{{ __('messages.company') }}<input name="company" placeholder="{{ __('messages.company_name') }}"></label>
            <label>{{ __('messages.rating') }}
                <select name="rating">
                    @foreach (range(5, 1) as $r)
                        <option value="{{ $r }}" {{ old('rating', 5) == $r ? 'selected' : '' }}>{{ str_repeat('★', $r) . str_repeat('☆', 5 - $r) }} ({{ $r }})</option>
                    @endforeach
                </select>
            </label>
            <label>{{ __('messages.sort_order') }}<input name="sort_order" type="number" min="0" value="0"></label>
            <label>{{ __('messages.avatar') }} <small>(max 1MB)</small><input name="avatar" type="file" accept="image/*"></label>
        </div>
        <label>{{ __('messages.testimonial_body') }}<textarea name="body" rows="4" placeholder="{{ __('messages.testimonial_placeholder') }}" required></textarea></label>
        <label class="check-row"><input name="is_active" type="checkbox" value="1" checked> {{ __('messages.active') }}</label>
        <button class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.add_testimonial') }}</button>
    </form>
</section>

<section class="panel">
    <div class="section-head">
        <h2>{{ __('messages.testimonials') }} <span class="badge">{{ $testimonials->count() }}</span></h2>
    </div>

    @if ($testimonials->isEmpty())
        <div class="empty-state">
            <i class="fa-solid fa-quote-left"></i>
            <p>{{ __('messages.no_testimonials') }}</p>
        </div>
    @else
        <div class="testimonial-admin-list">
            @foreach ($testimonials as $testimonial)
                <article class="testimonial-admin-card {{ $testimonial->is_active ? '' : 'inactive' }}">
                    <div class="testimonial-admin-header">
                        @if ($testimonial->avatar)
                            <img src="{{ Storage::url($testimonial->avatar) }}" alt="{{ $testimonial->name }}" class="testimonial-avatar-sm">
                        @else
                            <span class="testimonial-avatar-placeholder"><i class="fa-solid fa-user"></i></span>
                        @endif
                        <div>
                            <strong>{{ $testimonial->name }}</strong>
                            @if ($testimonial->role || $testimonial->company)
                                <small>{{ collect([$testimonial->role, $testimonial->company])->filter()->implode(' · ') }}</small>
                            @endif
                            <div class="star-rating">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fa-{{ $i <= $testimonial->rating ? 'solid' : 'regular' }} fa-star"></i>
                                @endfor
                            </div>
                        </div>
                        <span class="status ml-auto">{{ $testimonial->is_active ? __('messages.active') : __('messages.inactive') }}</span>
                    </div>
                    <p class="testimonial-body-preview">"{{ $testimonial->body }}"</p>

                    <details class="edit-details">
                        <summary>{{ __('messages.edit') }}</summary>
                        <form method="POST" action="{{ route('super-admin.testimonials.update', $testimonial) }}" enctype="multipart/form-data" class="form-grid" style="margin-top:12px">
                            @csrf
                            @method('PATCH')
                            <div class="form-grid two">
                                <label>{{ __('messages.name') }}<input name="name" value="{{ $testimonial->name }}" required></label>
                                <label>{{ __('messages.role') }}<input name="role" value="{{ $testimonial->role }}"></label>
                                <label>{{ __('messages.company') }}<input name="company" value="{{ $testimonial->company }}"></label>
                                <label>{{ __('messages.rating') }}
                                    <select name="rating">
                                        @foreach (range(5, 1) as $r)
                                            <option value="{{ $r }}" {{ $testimonial->rating == $r ? 'selected' : '' }}>{{ str_repeat('★', $r) . str_repeat('☆', 5 - $r) }} ({{ $r }})</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label>{{ __('messages.sort_order') }}<input name="sort_order" type="number" min="0" value="{{ $testimonial->sort_order }}"></label>
                                <label>{{ __('messages.avatar') }}<input name="avatar" type="file" accept="image/*"></label>
                            </div>
                            <label>{{ __('messages.testimonial_body') }}<textarea name="body" rows="3" required>{{ $testimonial->body }}</textarea></label>
                            <label class="check-row"><input name="is_active" type="checkbox" value="1" {{ $testimonial->is_active ? 'checked' : '' }}> {{ __('messages.active') }}</label>
                            <div class="card-actions">
                                <button class="secondary-button"><i class="fa-solid fa-check"></i> {{ __('messages.save_changes') }}</button>
                            </div>
                        </form>
                    </details>

                    <form method="POST" action="{{ route('super-admin.testimonials.destroy', $testimonial) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                        @csrf
                        @method('DELETE')
                        <button class="danger-button"><i class="fa-solid fa-trash"></i> {{ __('messages.delete') }}</button>
                    </form>
                </article>
            @endforeach
        </div>
    @endif
</section>
@endsection
