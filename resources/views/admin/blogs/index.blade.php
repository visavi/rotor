@extends('layout')

@section('title')
    Блоги
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Блоги</li>
        </ol>
    </nav>
@stop

@section('content')
    @foreach ($categories as $key => $data)

        <div class="b">
            <i class="fa fa-folder-open"></i> <b><a href="/admin/blogs/{{ $data->id }}">{{ $data->name }}</a></b>

            @if ($data->new)
                ({{ $data->count_blogs }}/<span style="color:#ff0000">+{{ $data->new->count_blogs }}</span>)
            @else
                ({{ $data->count_blogs }})
            @endif

            @if (isAdmin('boss'))
                <div class="float-right">
                    <a href="/admin/blogs/edit/{{ $data->id }}"><i class="fa fa-pencil-alt"></i></a>
                    <a href="/admin/blogs/delete/{{ $data->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы уверены что хотите удалить данный раздел?')"><i class="fa fa-times"></i></a>
                </div>
            @endif
        </div>

        <div>
            @if ($data->children->isNotEmpty())
                @foreach ($data->children as $child)
                    <i class="fa fa-angle-right"></i> <b><a href="/admin/blogs/{{ $child->id }}">{{ $child->name }}</a></b>
                    @if ($child->new)
                        ({{ $child->count_blogs }}/<span style="color:#ff0000">+{{ $child->new->count_blogs }}</span>)
                    @else
                        ({{ $child->count_blogs }})
                    @endif

                    @if (isAdmin('boss'))
                        <a href="/admin/blogs/edit/{{ $child->id }}"><i class="fa fa-pencil-alt"></i></a>
                        <a href="/admin/blogs/delete/{{ $child->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы уверены что хотите удалить данный раздел?')"><i class="fa fa-times"></i></a>
                    @endif
                    <br/>
                @endforeach
            @endif
        </div>
    @endforeach

    @if (isAdmin('boss'))
        <div class="form my-3">
            <form action="/admin/blogs/create" method="post">
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

        <i class="fa fa-sync"></i> <a href="/admin/blogs/restatement?token={{ $_SESSION['token'] }}">Пересчитать</a><br>
    @endif
@stop
