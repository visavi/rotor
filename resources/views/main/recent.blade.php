@section('title')
    {{ __('index.recent_activity') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.recent_activity') }}</li>
        </ol>
    </nav>
@stop

<div class="b"><i class="fab fa-forumbee fa-lg text-muted"></i> <b>{{ __('index.recent_topics') }}</b></div>
{{ recentTopics() }}

<div class="b"><i class="fa fa-download fa-lg text-muted"></i> <b>{{ __('index.recent_files') }}</b></div>
{{ recentDowns() }}

<div class="b"><i class="fa fa-globe fa-lg text-muted"></i> <b>{{ __('index.recent_articles') }}</b></div>
{{ recentArticles() }}

<div class="b"><i class="fa fa-image fa-lg text-muted"></i> <b>{{ __('index.recent_photos') }}</b></div>
{{  recentPhotos() }}
