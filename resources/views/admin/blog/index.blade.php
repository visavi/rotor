@extends('layout')

@section('title')
    Управление блогами
@stop

@section('content')

    <h1>Управление блогами</h1>

    @foreach ($categories as $key => $data)

        <div class="b">
            <i class="fa fa-folder-open"></i> <b><a href="/admin/blog/{{ $data->id }}">{{ $data->name }}</a></b>

            @if ($data->new)
                ({{ $data->count_blogs }}/<span style="color:#ff0000">+{{ $data->new->count_blogs }}</span>)
            @else
                ({{ $data->count_blogs }})
            @endif

            @if (isAdmin('boss'))
                <div class="float-right">
                    <a href="/admin/blog/edit/{{ $data->id }}"><i class="fa fa-pencil-alt"></i></a>
                    <a href="/admin/blog/delete/{{ $data->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы уверены что хотите удалить данный раздел?')"><i class="fa fa-times"></i></a>
                </div>
            @endif
        </div>

        <div>
            @if ($data->children->isNotEmpty())
                @foreach ($data->children as $child)
                    <i class="fa fa-angle-right"></i> <b><a href="/admin/blog/{{ $child->id }}">{{ $child->name }}</a></b>
                    @if ($child->new)
                        ({{ $child->count_blogs }}/<span style="color:#ff0000">+{{ $child->new->count_blogs }}</span>)
                    @else
                        ({{ $child->count_blogs }})
                    @endif

                    @if (isAdmin('boss'))
                        <a href="/admin/blog/edit/{{ $child->id }}"><i class="fa fa-pencil-alt"></i></a>
                        <a href="/admin/blog/delete/{{ $child->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы уверены что хотите удалить данный раздел?')"><i class="fa fa-times"></i></a>
                    @endif
                    <br/>
                @endforeach
            @endif
        </div>
    @endforeach

    @if (isAdmin('boss'))
        <div class="form my-3">
            <form action="/admin/blog/create" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                <div class="form-inline">
                    <div class="form-group{{ hasError('name') }}">
                        <input type="text" class="form-control" id="name" name="name" maxlength="50" value="{{ getInput('name') }}" placeholder="Раздел" required>
                    </div>

                    <button class="btn btn-primary">Создать раздел</button>
                </div>
                {!! textError('name') !!}
            </form>
        </div>

        <i class="fa fa-sync"></i> <a href="/admin/blog/restatement?token={{ $_SESSION['token'] }}">Пересчитать</a><br>
    @endif

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
