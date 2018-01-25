@extends('layout')

@section('title')
    Ожидающие регистрации
@stop

@section('content')

    <h1>Ожидающие регистрации</h1>

    <div class="mb-3 font-weight-bold">

        <i class="fa fa-exclamation-circle"></i>

        @if (setting('regkeys'))
            <span class="text-success">Включено подтверждение регистраций!</span>
        @else
            <span class="text-danger">Подтверждение регистрации выключено!</span>
        @endif
    </div>

    @if ($users->isNotEmpty())

       <form action="/admin/reglist?page={{ $page['current'] }}" method="post">
           <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach ($users as $user)
               <div class="b">
                   <input type="checkbox" name="choice[]" value="{{ $user->id }}">
                    {!! $user->getGender() !!} <b>{!! profile($user) !!}</b>
                   (email: {{ $user->email }})
               </div>

               <div>Зарегистрирован: {{ dateFixed($user->created_at, 'd.m.Y') }}</div>
            @endforeach

           <?php $inputAction = getInput('action'); ?>
           <div class="form-inline mt-3">
               <div class="form-group{{ hasError('action') }}">
                   <select class="form-control" name="action">
                       <option>Выберите действие</option>
                       <option value="yes"{{ $inputAction === 'yes' ? ' selected' : '' }}>Разрешить</option>
                       <option value="no"{{ $inputAction === 'no' ? ' selected' : '' }}>Запретить</option>
                   </select>
               </div>

               <button class="btn btn-primary">Выполнить</button>
           </div>
           {!! textError('action') !!}
       </form>

        {!! pagination($page) !!}

       Всего ожидающих: <b>{{ $page['total'] }}</b><br><br>

    @else
        {!! showError('Нет пользователей требующих подтверждения регистрации!!') !!}
    @endif

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
