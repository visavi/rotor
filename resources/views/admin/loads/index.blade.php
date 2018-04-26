@extends('layout')

@section('title')
    Загрузки
@stop

@section('content')

    <h1>Загрузки</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Загрузки</li>
        </ol>
    </nav>

    @if ($categories->isNotEmpty())
        @foreach ($categories as $category)
            <div class="b">
                <i class="fa fa-folder-open"></i>
                <b><a href="/admin/loads/{{ $category->id }}">{{ $category->name }}</a></b>
                @if ($category->new)
                    ({{ $category->count_downs }}/<span style="color:#ff0000">+{{ $category->new->count_downs }}</span>)
                @else
                    ({{ $category->count_downs }})
                @endif

                @if (isAdmin('boss'))
                    <div class="float-right">
                        <a href="/admin/loads/edit/{{ $category->id }}"><i class="fa fa-pencil-alt"></i></a>
                        <a href="/admin/loads/delete/{{ $category->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы уверены что хотите удалить данный раздел?')"><i class="fa fa-times"></i></a>
                    </div>
                @endif
            </div>

            <div>
                @if ($category->children->isNotEmpty())
                    @foreach ($category->children as $child)
                        <i class="fa fa-angle-right"></i> <b><a href="/admin/loads/{{ $child->id }}">{{ $child['name'] }}</a></b>
                        @if ($child->new)
                            ({{ $child->count_downs }}/<span style="color:#ff0000">+{{ $child->new->count_downs }}</span>)
                        @else
                            ({{ $child->count_downs }})
                        @endif

                        @if (isAdmin('boss'))
                                <a href="/admin/loads/edit/{{ $child->id }}"><i class="fa fa-pencil-alt"></i></a>
                                <a href="/admin/loads/delete/{{ $child->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы уверены что хотите удалить данный раздел?')"><i class="fa fa-times"></i></a>
                        @endif
                        <br>
                    @endforeach
                @endif
            </div>
        @endforeach
    @else
        {!! showError('Разделы загрузок еще не созданы!') !!}
    @endif

    @if (isAdmin('boss'))
        <div class="form my-3">
            <form action="/admin/loads/create" method="post">
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

        <i class="fa fa-sync"></i> <a href="/admin/loads/restatement?token={{ $_SESSION['token'] }}">Пересчитать</a><br>
    @endif
@stop
