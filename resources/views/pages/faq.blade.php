@extends('layout')

@section('title', __('pages.faq'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('pages.faq') }}</li>
        </ol>
    </nav>
@stop

@php
$questions = [
    'why_register',
    'how_is_registration',
    'why_do_you_need_status_and_reputation',
    'what_will_give_me_status',
    'how_can_i_help_site',
    'did_not_find_answer',
];
@endphp

@section('content')
    @if ($abilities->isNotEmpty())
        <div class="section mb-3 shadow">
            <div class="section-title"><i class="fas fa-unlock-keyhole"></i> {{ __('pages.faq_active') }}</div>

            <div class="section-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <tbody>
                            @foreach ($abilities as $ability)
                                <tr>
                                    <td class="text-nowrap"><span class="badge bg-adaptive">{{ plural($ability['point'], setting('scorename')) }}</span></td>
                                    <td class="w-100">{{ $ability['text'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-muted mt-2">{{ __('pages.faq_active_text9') }}</div>
            </div>
        </div>
    @endif

    @if ($rewards->isNotEmpty())
        <div class="section mb-3 shadow">
            <div class="section-title"><i class="fas fa-coins"></i> {{ __('pages.faq_money') }}</div>

            <div class="section-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <tbody>
                            @foreach ($rewards as $reward)
                                <tr>
                                    <td class="w-100">{{ $reward['text'] }}</td>
                                    <td class="text-nowrap text-end">
                                        @if ($reward['point'])
                                            <span class="badge bg-adaptive">+{{ plural($reward['point'], setting('scorename')) }}</span>
                                        @endif

                                        @if ($reward['money'])
                                            <span class="badge bg-adaptive">+{{ plural($reward['money'], setting('moneyname')) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Открыт один вопрос: name объединяет <details> в группу --}}
    <div class="accordion mb-3">
        @foreach ($questions as $index => $question)
            <details class="accordion-item" name="faq" @if (! $index) open @endif>
                <summary class="accordion-button">{{ __('pages.' . $question . '_title') }}</summary>
                <div class="accordion-body">{!! __('pages.' . $question) !!}</div>
            </details>
        @endforeach
    </div>
@stop
