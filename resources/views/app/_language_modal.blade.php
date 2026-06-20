<div class="modal fade" id="languageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-globe-americas"></i> {{ __('users.language') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('main.close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    @foreach (getAvailableLanguages() as $lang)
                        @php($name = __('main.lang', [], $lang))
                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center{{ $lang === app()->getLocale() ? ' active' : '' }}" data-lang="{{ $lang }}">
                            <span>
                                <img src="/assets/flags/{{ $lang }}.svg" alt="{{ $lang }}" width="24" class="me-2 flag" onerror="this.remove()">
                                {{ $name === 'main.lang' ? strtoupper($lang) : $name }}
                            </span>
                            @if ($lang === app()->getLocale())
                                <i class="fas fa-check"></i>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
