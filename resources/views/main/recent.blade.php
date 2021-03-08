@section('title', __('index.recent_activity'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.recent_activity') }}</li>
        </ol>
    </nav>
@stop

<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="fab fa-forumbee fa-lg text-muted"></i>
        {{ __('index.recent_topics') }}
    </div>
    {{ recentTopics() }}
</div>

<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="fa fa-download fa-lg text-muted"></i>
        {{ __('index.recent_files') }}
    </div>
    {{ recentDowns() }}
</div>

<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="fa fa-globe fa-lg text-muted"></i>
        {{ __('index.recent_articles') }}
    </div>
    {{ recentArticles() }}
</div>

<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="fa fa-image fa-lg text-muted"></i>
        {{ __('index.recent_photos') }}
    </div>
    {{ recentPhotos() }}
</div>
