@extends('layout')

@section('title', __('index.social_networks'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.social_networks') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($socials->isNotEmpty())
        @foreach ($socials as $social)
            <div class="section-form mb-3 shadow">
                <i class="fas fa-chevron-circle-right"></i> <b>{{ $social->network }}</b> ({{ __('main.added') }}: {{ dateFixed($social->created_at) }})

                <div class="float-end">
                    <form action="/socials/delete/{{ $social->id }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('socials.text_confirm') }}')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-link p-0"><i class="fas fa-times"></i></button>
                    </form>
                </div>
            </div>
        @endforeach

        {{ __('main.total') }}: <b>{{ $socials->count() }}</b><br>

    @else
        {{ showError(__('socials.empty_records')) }}
    @endif
@stop
