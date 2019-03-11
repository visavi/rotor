@extends('layout')

@section('title')
    {{ trans('pages.faq') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('pages.faq') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <b>{{ trans('pages.faq_register') }}</b><br>
    {{ trans('pages.faq_register1') }}<br>
    {{ trans('pages.faq_register2') }}:<br>

    <b>1</b>. {{ trans('pages.faq_register_text1') }}<br>
    <b>2</b>. {{ trans('pages.faq_register_text2') }}<br>
    <b>3</b>. {{ trans('pages.faq_register_text3') }}<br>
    <b>4</b>. {{ trans('pages.faq_register_text4') }}<br>
    <b>5</b>. {{ trans('pages.faq_register_text5') }}<br>
    <b>6</b>. {{ trans('pages.faq_register_text6') }}<br>
    <b>7</b>. {{ trans('pages.faq_register_text7') }}<br>
    <b>8</b>. {{ trans('pages.faq_register_text8') }}<br>
    <b>9</b>. {{ trans('pages.faq_register_text9') }}<br>

    <br>{{ trans('pages.faq_active') }}:<br>

    @if (setting('rekuserpoint'))
        <b>{{ plural(setting('rekuserpoint'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text1') }}<br>
    @endif

    @if (setting('privatprotect'))
        <b>{{ plural(setting('privatprotect'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text2') }}<br>
    @endif


    @if (setting('addofferspoint'))
        <b>{{ plural(setting('addofferspoint'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text3') }}<br>
    @endif

    @if (setting('forumloadpoints'))
        <b>{{ plural(setting('forumloadpoints'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text4') }}<br>
    @endif

    @if (setting('sendmoneypoint'))
        <b>{{ plural(setting('sendmoneypoint'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text5') }}<br>
    @endif

    @if (setting('editratingpoint'))
        <b>{{ plural(setting('editratingpoint'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text6') }}<br>
    @endif

    @if (setting('editforumpoint'))
        <b>{{ plural(setting('editforumpoint'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text7') }}<br>
    @endif

    @if (setting('advertpoint'))
        <b>{{ plural(setting('advertpoint'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text8') }}<br>
    @endif

    {{ trans('pages.faq_active_text9') }}<br>

    <br>

    <b>Как проходит регистрация</b><br>
    <b>1</b>. Вводите желаемый логин и пароль<br>
    <b>2</b>. Указываете свой email и проверочный код<br>
    <b>3</b>. Нажимаете кнопку регистрации и создается ваш профиль<br>
    <b>4</b>. Теперь если включена функция подтверждения регистрации, то вам на email будет выслан код подтверждения, который необходим для окончания регистрации<br>
    <b>5</b>. Если подтверждение регистрации выключено, то после входа на сайт вы становитесь полноправным пользователем сайта<br>
    <b>6</b>. Теперь вы можете добавить побольше информации о себе в профиле, а также изменить свои настройки<br>

    <br><b>Зачем нужен статус и репутацию</b><br>
    Статус нужен для того, чтобы оценить вашу активность на сайте. За каждое сообщение в гостевой, форуме, комментариях начисляется актив. Чем больше актива, тем выше статус.<br>
    Репутация нужен для того, чтобы показать ваше значение на сайте. Чем больше у вас положительных голосов, тем больше доверия к вам<br>

    <br><b>Что мне даст высокий статус</b><br>
    Самых активных, инициативных и старающихся пользователей могут взять в команду администрации сайта (конечно если у вас есть желание). Но войти в команду не так легко, так как вакансии ограничены. Старайтесь не нарушать правила и у вас будет возможность. Самые активные пользователи всегда находятся на первых местах рейтингах.<br>

    <br><b>Как я могу повлиять на дальнейшее развитие сайта</b><br>
    Активно участвуйте во всем, чаще заходите на сайт, советуйте сайт одноклассникам, одногруппникам, друзьям, знакомым и всем тем кто знает что такое интернет. К нам можно легко зайти как с компьютера так и с мобильного телефона или КПК, так как сайт имеет Wap и Web форматы<br>

    <br><b>Не нашли ответа на интересующий себя вопрос?</b><br>
    Напишите об этом <a href="/mails">администратору</a>, <a href="/administrators">старшим сайта</a> через внутреннюю почту или создавайте тему на форуме где будем вместе обсуждать вопрос, делиться опытом и знаниями<br><br>

@stop
