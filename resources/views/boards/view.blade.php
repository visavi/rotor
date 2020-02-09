@extends('layout')

@section('title')
    {{ $item->title }}
@stop

@section('header')
    @if ($item->user->id === getUser('id'))
        <div class="float-right">
            <a class="btn btn-success" href="/items/edit/{{ $item->id }}">{{ __('main.change') }}</a>
        </div><br>
    @endif

    <h1>{{ $item->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/boards">{{ __('index.boards') }}</a></li>

            @if ($item->category->parent->id)
                <li class="breadcrumb-item"><a href="/boards/{{ $item->category->parent->id }}">{{ $item->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/boards/{{ $item->category->id }}">{{ $item->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ $item->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($item->expires_at <= SITETIME)
        <div class="alert alert-danger">{{ __('boards.item_not_active') }}</div>
    @endif

    @if (isAdmin())
        <div>
            <a href="/admin/items/edit/{{ $item->id }}">{{ __('main.edit') }}</a> /
            <a href="/admin/items/delete/{{ $item->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('boards.confirm_delete_item') }}')">{{ __('main.delete') }}</a>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">

                    @if ($item->files->isNotEmpty())
                        <div class="row mb-3">
                            <div class="col-md-12">

                                <?php $countFiles = $item->files->count() ?>
                                <div id="myCarousel" class="carousel slide media-file" data-ride="carousel">
                                    @if ($countFiles > 1)
                                        <ol class="carousel-indicators">
                                            @for ($i = 0; $i < $countFiles; $i++)
                                                <li data-target="#myCarousel" data-slide-to="{{ $i }}"{!! empty($i) ? ' class="active"' : '' !!}></li>
                                            @endfor
                                        </ol>
                                    @endif

                                    <div class="carousel-inner">
                                        @foreach ($item->files as $file)
                                            <div class="carousel-item{{ $loop->first ? ' active' : '' }}">
                                                {!! resizeImage($file->hash, ['alt' => $item->title, 'class' => 'd-block w-100']) !!}
                                            </div>
                                        @endforeach
                                    </div>

                                    @if ($countFiles > 1)
                                        <a class="carousel-control-prev" href="#myCarousel" role="button" data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                        <a class="carousel-control-next" href="#myCarousel" role="button" data-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-10">
                            <div class="message">{!! bbCode($item->text) !!}</div>
                            <p>

                                @if ($item->phone)
                                    <span class="badge badge-pill badge-primary">{{ __('boards.phone') }}: {{ $item->phone }}</span><br>
                                @endif

                                <i class="fa fa-user-circle"></i> {!! $item->user->getProfile() !!} / {{ dateFixed($item->updated_at) }}<br>

                                @if ($item->expires_at > SITETIME)
                                    <i class="fas fa-clock"></i> {{ __('boards.expires_in') }} {{ formatTime($item->expires_at - SITETIME) }}
                                @endif
                            </p>
                        </div>

                        <div class="col-md-2">
                            @if ($item->price)
                                <button type="button" class="btn btn-info">{{ $item->price }} {{ setting('currency') }}</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
