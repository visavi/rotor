@extends('layout')

@section('title')
    Объявления
@stop

@section('content')

    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/items/create">Добавить объявление</a><br>
        </div><br>
    @endif

    <h1>Объявления</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Объявления</li>
        </ol>
    </nav>

    @if ($items->isNotEmpty())
        @foreach ($items as $item)
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="/items/{{ $item->id }}">{!! $item->getFirstImage() !!}</a>
                                </div>
                                <div class="col-md-7">
                                    <h5><a href="/items/{{ $item->id }}">{{ $item->title }}</a></h5>
                                    <small>
                                        @if ($item->category->parent->id)
                                            <i class="fas fa-angle-right"></i> <a href="/boards/{{ $item->category->parent->id }}">{{ $item->category->parent->name }}</a>
                                        @endif

                                            <i class="fas fa-angle-right"></i> <a href="/boards/{{ $item->category->id }}">{{ $item->category->name }}</a>
                                    </small>
                                    <div class="message">{!! $item->cutText() !!}</div>
                                    <p><i class="fa fa-user-circle"></i> {!! $item->user->getProfile() !!}</p>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-success">{{ $item->price }} ₽</button><br>
                                    <small>{{ dateFixed($item->created_at) }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <?php var_dump(Illuminate\Support\Str::words('The quick brown fox jumps over the lazy dog', 3)) ?>

        {!! pagination($page) !!}

    @else
        {!! showError('Объявлений еще нет!') !!}
    @endif

@stop
