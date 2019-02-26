@section('title')
    {{ trans('index.useful_information') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('index.useful_information') }}</li>
        </ol>
    </nav>
@stop

<i class="far fa-circle fa-lg text-muted"></i> <a href="/rules">{{ trans('index.site_rules') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/tags">{{ trans('index.tag_help') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/stickers">{{ trans('index.stickers_help') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/faq">{{ trans('index.faq') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/api">{{ trans('index.api_interface') }}</a><br>

<i class="far fa-circle fa-lg text-muted"></i> <a href="/ratinglists">{{ trans('index.riches_rating') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/authoritylists">{{ trans('index.reputation_rating') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/statusfaq">{{ trans('index.user_statuses') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/who">{{ trans('index.who_online') }}</a><br>
