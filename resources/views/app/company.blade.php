@extends('layouts.app', ['title' => __('messages.company'), 'heading' => __('messages.company')])

@section('content')
<section class="panel">
    @include('partials.errors')
    <form method="POST" action="{{ route('company.update') }}" class="form-grid" enctype="multipart/form-data">
        @csrf

        {{-- ── Logo ── --}}
        <div style="grid-column:1/-1;display:flex;align-items:center;gap:20px;padding:18px;background:#f9fafb;border:1.5px dashed #dde3ea;border-radius:12px">
            <div id="logo-preview-wrap" style="width:80px;height:80px;border-radius:12px;overflow:hidden;background:#e8f0f3;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid #dde3ea">
                @if(!empty($company->logo))
                    <img id="logo-preview" src="{{ Storage::disk('public')->url($company->logo) }}" style="width:100%;height:100%;object-fit:contain" alt="logo">
                @else
                    <i id="logo-placeholder" class="fa-solid fa-image" style="font-size:28px;color:#8fa4ae"></i>
                    <img id="logo-preview" src="" style="width:100%;height:100%;object-fit:contain;display:none" alt="logo">
                @endif
            </div>
            <div style="flex:1">
                <p style="margin:0 0 4px;font-size:13px;font-weight:600;color:#17202a">Company Logo</p>
                <p style="margin:0 0 10px;font-size:12px;color:#60757f">JPG, PNG, WebP, or SVG. Max 2 MB. Displayed in invoices and reports.</p>
                <label style="display:inline-flex;align-items:center;gap:7px;padding:7px 14px;background:#fff;border:1.5px solid #dde3ea;border-radius:8px;cursor:pointer;font-size:13px;font-weight:500">
                    <i class="fa-solid fa-upload"></i> Choose file
                    <input type="file" name="logo" id="logo-input" accept="image/*" style="display:none">
                </label>
                <span id="logo-filename" style="margin-left:10px;font-size:12px;color:#8fa4ae"></span>
            </div>
        </div>
        <p class="form-section-label" style="grid-column:1/-1;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#60757f;margin:0 0 2px">
            <i class="fa-solid fa-building"></i> Company Identity
        </p>

        <label style="grid-column:1/-1">
            {{ __('messages.company_name') }}
            <input name="name" value="{{ old('name', $company->name ?? auth()->user()->business->name) }}" placeholder="PT. Example Indonesia" required>
        </label>

        <label>
            Email
            <input type="email" name="email" value="{{ old('email', $company->email ?? '') }}" placeholder="info@company.com">
        </label>

        <label>
            Call Center / Phone
            <input name="call_center" value="{{ old('call_center', $company->call_center ?? '') }}" placeholder="021-12345678 or 0800-xxx">
        </label>

        <label>
            Business Field <span style="color:#8fa4ae;font-weight:400">(Bidang Usaha)</span>
            <input name="field" value="{{ old('field', $company->field ?? '') }}" placeholder="e.g. Retail, Manufacturing, Distribution">
        </label>

        <label style="grid-column:1/-1">
            Address
            <textarea name="address" rows="3" placeholder="Jl. Contoh No. 1, Jakarta Selatan 12345">{{ old('address', $company->address ?? '') }}</textarea>
        </label>

        <label style="grid-column:1/-1">
            About Company
            <textarea name="about" rows="4" placeholder="Short description of your company, products, and values...">{{ old('about', $company->about ?? '') }}</textarea>
        </label>

        {{-- ── Brand Story ── --}}
        <p class="form-section-label" style="grid-column:1/-1;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#60757f;margin:16px 0 2px">
            <i class="fa-solid fa-bullseye"></i> Brand Story
        </p>

        <label style="grid-column:1/-1">
            {{ __('messages.vision') }}
            <textarea name="vision" rows="3" placeholder="Our vision...">{{ old('vision', $company->vision ?? '') }}</textarea>
        </label>

        <label style="grid-column:1/-1">
            {{ __('messages.mission') }}
            <textarea name="mission" rows="3" placeholder="Our mission...">{{ old('mission', $company->mission ?? '') }}</textarea>
        </label>

        <label style="grid-column:1/-1">
            {{ __('messages.organization') }}
            <textarea name="organization" rows="3" placeholder="Organizational structure or team overview...">{{ old('organization', $company->organization ?? '') }}</textarea>
        </label>

        <label style="grid-column:1/-1">
            {{ __('messages.why_us') }}
            <textarea name="why_us" rows="3" placeholder="Why customers choose us...">{{ old('why_us', $company->why_us ?? '') }}</textarea>
        </label>

        <div style="grid-column:1/-1">
            <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save_settings') }}</button>
        </div>
    </form>
</section>

@push('scripts')
<script>
(function () {
    var input    = document.getElementById('logo-input');
    var preview  = document.getElementById('logo-preview');
    var placeholder = document.getElementById('logo-placeholder');
    var filename = document.getElementById('logo-filename');
    if (!input) return;
    input.addEventListener('change', function () {
        var file = input.files[0];
        if (!file) return;
        filename.textContent = file.name;
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });
})();
</script>
@endpush
@endsection

