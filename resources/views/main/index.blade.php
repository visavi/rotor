@section('title', __('index.information'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.information') }}</li>
        </ol>
    </nav>
@stop

<i class="far fa-circle fa-lg text-muted"></i> <a href="/rules">{{ __('index.site_rules') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/tags">{{ __('index.tag_help') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/stickers">{{ __('index.stickers_help') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/faq">{{ __('index.faq') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/api">{{ __('index.api_interface') }}</a><br>

<i class="far fa-circle fa-lg text-muted"></i> <a href="/ratinglists">{{ __('index.riches_rating') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/authoritylists">{{ __('index.reputation_rating') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/statusfaq">{{ __('index.user_statuses') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/who">{{ __('index.who_online') }}</a><br>
