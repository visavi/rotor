@extends('layout')

@section('title')
    {{ $item->title }}
@stop

@section('content')

    <h1>{{ $item->title }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/boards">Объявления</a></li>

            @if ($item->category->parent->id)
                <li class="breadcrumb-item"><a href="/boards/{{ $item->category->parent->id }}">{{ $item->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/boards/{{ $item->category->id }}">{{ $item->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ $item->title }}</li>
        </ol>
    </nav>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">

                    @if ($item->files->isNotEmpty())
                        <div class="row mb-3">
                            <div class="col-md-12">

                                <?php $countFiles = $item->files->count() ?>
                                <div id="myCarousel" class="carousel slide" data-ride="carousel">
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
                                                {!! resizeImage('/uploads/boards/' . $file->hash, ['alt' => $item->title, 'class' => 'd-block w-100']) !!}
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
                            <p><i class="fa fa-user-circle"></i> {!! $item->user->getProfile() !!} / {{ dateFixed($item->created_at) }}</p>
                        </div>

                        <div class="col-md-2">
                            @if ($item->price)
                                <button type="button" class="btn btn-primary">{{ $item->price }} ₽</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
