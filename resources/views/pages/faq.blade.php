@extends('layout')

@section('title', __('pages.faq'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('pages.faq') }}</li>
        </ol>
    </nav>
@stop

@php
// Возможности, открывающиеся при наборе актива: показываются только настроенные
$abilities = collect([
    ['point' => setting('rekuserpoint'),     'text' => __('pages.faq_active_text1')],
    ['point' => setting('privatprotect'),    'text' => __('pages.faq_active_text2')],
    ['point' => setting('addofferspoint'),   'text' => __('pages.faq_active_text3')],
    ['point' => setting('sendmoneypoint'),   'text' => __('pages.faq_active_text5')],
    ['point' => setting('editratingpoint'),  'text' => __('pages.faq_active_text6')],
    ['point' => setting('editforumpoint'),   'text' => __('pages.faq_active_text7')],
    ['point' => setting('advertpoint'),      'text' => __('pages.faq_active_text8')],
    ['point' => setting('editcolorpoint'),   'text' => __('pages.faq_active_text4')],
    ['point' => setting('editstatuspoint'),  'text' => __('pages.faq_active_text10')],
])->filter(fn ($item) => $item['point'])->sortBy('point');

// Начисления за действия: строка показывается, если задан актив или деньги
$rewards = collect([
    ['point' => setting('comment_point'),   'money' => setting('comment_money'),   'text' => __('pages.faq_money_comment')],
    ['point' => setting('guestbook_point'), 'money' => setting('guestbook_money'), 'text' => __('pages.faq_money_guestbook')],
    ['point' => setting('down_point'),      'money' => setting('down_money'),      'text' => __('pages.faq_money_down')],
    ['point' => setting('blog_point'),      'money' => setting('blog_money'),      'text' => __('pages.faq_money_blog')],
    ['point' => setting('forum_point'),     'money' => setting('forum_money'),     'text' => __('pages.faq_money_forum')],
    ['point' => 0,                          'money' => setting('registermoney'),   'text' => __('pages.faq_money_register')],
    ['point' => 0,                          'money' => setting('bonusmoney'),      'text' => __('pages.faq_money_bonus')],
])->filter(fn ($item) => $item['point'] || $item['money']);

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
                                            <span class="badge bg-adaptive">+{{ plural((int) $reward['point'], setting('scorename')) }}</span>
                                        @endif

                                        @if ($reward['money'])
                                            <span class="badge bg-adaptive">+{{ plural((int) $reward['money'], setting('moneyname')) }}</span>
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

    <div class="accordion mb-3" id="faqAccordion">
        @foreach ($questions as $index => $question)
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button{{ $index ? ' collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $index }}">
                        {{ __('pages.' . $question . '_title') }}
                    </button>
                </h2>

                <div id="faq-{{ $index }}" class="accordion-collapse collapse{{ $index ? '' : ' show' }}" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">{!! __('pages.' . $question) !!}</div>
                </div>
            </div>
        @endforeach
    </div>
@stop
