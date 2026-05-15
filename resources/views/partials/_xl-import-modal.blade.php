{{--
  Reusable import modal partial.
  Variables: $modalId, $title, $importRoute, $columns
--}}
<div class="modal-overlay" id="{{ $modalId }}" role="dialog" aria-modal="true">
    <div class="modal-card">
        <header>
            <h3><i class="fa-solid fa-file-import"></i> {{ __('messages.import') }}: {{ $title }}</h3>
            <a href="#" class="icon-button" aria-label="{{ __('messages.close') }}"><i class="fa-solid fa-xmark"></i></a>
        </header>
        <form method="POST" action="{{ $importRoute }}" enctype="multipart/form-data" class="form-grid">
            @csrf
            <p style="margin:0;color:var(--muted);font-size:.875rem">
                {{ __('messages.import_excel_hint', ['columns' => $columns]) }}
            </p>
            <label>
                <span class="label-cap">{{ __('messages.file') }} <span class="req">*</span></span>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
            </label>
            <div class="modal-actions">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button"><i class="fa-solid fa-upload"></i> {{ __('messages.import') }}</button>
            </div>
        </form>
    </div>
</div>
