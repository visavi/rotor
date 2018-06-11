@extends('layout')

@section('title')
    {{ trans('socials.title') }}
@stop

@section('content')

    <h1>{{ trans('socials.title') }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ trans('common.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('socials.title') }}</li>
        </ol>
    </nav>

    @if ($socials->isNotEmpty())
        @foreach ($socials as $social)
            <div class="post">
                <div class="b">
                    <i class="fas fa-chevron-circle-right"></i> <b>{{ $social->network }}</b> ({{ trans('socials.added') }}: {{ dateFixed($social->created_at) }})

                    <div class="float-right">
                        <a href="/socials/delete/{{ $social->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить привязку')"><i class="fas fa-times"></i></a>
                    </div>
                </div>
            </div>
        @endforeach

        {{ trans('socials.total') }}: <b>{{ $socials->count() }}</b><br><br>

    @else
        {!! showError(trans('socials.empty_records')) !!}
    @endif

@stop
