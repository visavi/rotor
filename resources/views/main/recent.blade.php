@section('title')
    {{ trans('index.recent_activity') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('index.recent_activity') }}</li>
        </ol>
    </nav>
@stop

<div class="b"><i class="fab fa-forumbee fa-lg text-muted"></i> <b>{{ trans('index.recent_topics') }}</b></div>
{{ recentTopics() }}

<div class="b"><i class="fa fa-download fa-lg text-muted"></i> <b>{{ trans('index.recent_files') }}</b></div>
{{ recentFiles() }}

<div class="b"><i class="fa fa-globe fa-lg text-muted"></i> <b>{{ trans('index.recent_articles') }}</b></div>
{{ recentBlogs() }}

<div class="b"><i class="fa fa-image fa-lg text-muted"></i> <b>{{ trans('index.recent_photos') }}</b></div>
{{  recentPhotos() }}
