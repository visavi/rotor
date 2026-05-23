<div class="text-center rounded-3 mb-4 p-5 position-relative overflow-hidden"
     style="background:linear-gradient(135deg,#6f42c1 0%,#1a73e8 40%,#00c6ff 100%);color:#fff;">

    {{-- animated shimmer overlay --}}
    <div class="position-absolute top-0 start-0 w-100 h-100" aria-hidden="true" style="
        background:linear-gradient(120deg,transparent 20%,rgba(255,255,255,.12) 50%,transparent 80%);
        background-size:200% 100%;
        animation:hero-shimmer 3s linear infinite;
        pointer-events:none;
    "></div>

    <div class="position-relative">
        <div class="mb-3">
            <i class="fas fa-rocket" style="font-size:3rem;filter:drop-shadow(0 2px 8px rgba(0,0,0,.25));"></i>
        </div>
        <h2 class="fw-bold mb-2" style="font-size:1.9rem;text-shadow:0 2px 8px rgba(0,0,0,.2);">{{ setting('title') }}</h2>
        <p class="mb-0" style="opacity:.9;font-size:1rem;text-shadow:0 1px 4px rgba(0,0,0,.15);">{{ __('welcome.slogan') }}</p>
    </div>
</div>

<style>
@keyframes hero-shimmer {
    0%   { background-position: -100% 0; }
    100% { background-position:  200% 0; }
}
</style>

<p class="text-muted text-center mb-4" style="font-size:.95rem;">
    {{ __('welcome.feed_hint') }}
</p>

<div class="row g-3 mb-5">

    <div class="col-md-4">
        <div class="d-flex align-items-start gap-3 rounded-3 border p-3 shadow-sm h-100">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-white fw-bold"
                 style="width:40px;height:40px;background:#1a73e8;font-size:1rem;">1</div>
            <div class="flex-grow-1">
                <div class="fw-semibold mb-1">{{ __('welcome.step1_title') }}</div>
                <div class="text-muted small mb-2">{{ __('welcome.step1_text') }}</div>
                @if (isAdmin())
                    <a href="/admin/modules" class="btn btn-sm btn-outline-primary text-nowrap">
                        <i class="fas fa-puzzle-piece me-1"></i>{{ __('welcome.step1_btn') }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="d-flex align-items-start gap-3 rounded-3 border p-3 shadow-sm h-100">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-white fw-bold"
                 style="width:40px;height:40px;background:#1a73e8;font-size:1rem;">2</div>
            <div class="flex-grow-1">
                <div class="fw-semibold mb-1">{{ __('welcome.step2_title') }}</div>
                <div class="text-muted small mb-2">{{ __('welcome.step2_text') }}</div>
                @if (isAdmin())
                    <a href="/admin/settings" class="btn btn-sm btn-outline-primary text-nowrap">
                        <i class="fas fa-sliders-h me-1"></i>{{ __('welcome.step2_btn') }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="d-flex align-items-start gap-3 rounded-3 border p-3 shadow-sm h-100">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-white fw-bold"
                 style="width:40px;height:40px;background:#1a73e8;font-size:1rem;">3</div>
            <div class="flex-grow-1">
                <div class="fw-semibold mb-1">{{ __('welcome.step3_title') }}</div>
                <div class="text-muted small">{{ __('welcome.step3_text') }}</div>
            </div>
        </div>
    </div>

</div>

<h5 class="fw-semibold mb-3">{{ __('welcome.features_title') }}</h5>

<div class="row g-3">

    @php
    $features = [
        ['icon' => 'fas fa-comments',     'color' => '#6f42c1', 'key' => 'forum'],
        ['icon' => 'fas fa-pen-nib',       'color' => '#0d6efd', 'key' => 'blog'],
        ['icon' => 'fas fa-newspaper',     'color' => '#0dcaf0', 'key' => 'news'],
        ['icon' => 'fas fa-images',        'color' => '#fd7e14', 'key' => 'photo'],
        ['icon' => 'fas fa-stream',        'color' => '#20c997', 'key' => 'wall'],
        ['icon' => 'fas fa-gamepad',       'color' => '#d63384', 'key' => 'games'],
        ['icon' => 'fas fa-gift',          'color' => '#dc3545', 'key' => 'gifts'],
        ['icon' => 'fas fa-book-open',     'color' => '#6c757d', 'key' => 'guestbook'],
        ['icon' => 'fas fa-envelope',      'color' => '#0d6efd', 'key' => 'messages'],
        ['icon' => 'fas fa-star',          'color' => '#ffc107', 'key' => 'rating'],
        ['icon' => 'fas fa-bullhorn',      'color' => '#fd7e14', 'key' => 'adverts'],
        ['icon' => 'fas fa-code',          'color' => '#6c757d', 'key' => 'api'],
    ];
    @endphp

    @foreach ($features as $f)
        <div class="col-6 col-md-3">
            <div class="d-flex align-items-start gap-2 rounded-3 border p-3 shadow-sm h-100">
                <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0 text-white"
                     style="width:36px;height:36px;background:{{ $f['color'] }};font-size:.85rem;">
                    <i class="{{ $f['icon'] }}"></i>
                </div>
                <div>
                    <div class="fw-semibold" style="font-size:.9rem;">{{ __('welcome.feat_' . $f['key'] . '_title') }}</div>
                    <div class="text-muted" style="font-size:.78rem;line-height:1.3;">{{ __('welcome.feat_' . $f['key'] . '_text') }}</div>
                </div>
            </div>
        </div>
    @endforeach

</div>
