@extends('layout')

@section('title', __('index.user_cleaning'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.user_cleaning') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($users->isEmpty())
        {{ __('admin.delusers.condition') }}:<br>

        <div class="section-form p-3 shadow">
            <form action="/admin/delusers" method="post">

                <div class="form-group">
                    <label for="period">{{ __('main.period') }}:</label>
                    <select class="form-control" id="period" name="period">
                        <option value="1825">{{ formatTime(1825 * 86400) }}</option>
                        <option value="1460">{{ formatTime(1460 * 86400) }}</option>
                        <option value="1095">{{ formatTime(1095 * 86400) }}</option>
                        <option value="730">{{ formatTime(730 * 86400) }}</option>
                        <option value="365">{{ formatTime(365 * 86400) }}</option>
                        <option value="180">{{ formatTime(180 * 86400) }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="point">{{ __('admin.delusers.minimum_asset') }}:</label>
                    <input type="text" class="form-control" id="point" name="point"  value="0" required>
                </div>

                <button class="btn btn-primary">{{ __('main.analysis') }}</button>
            </form>
        </div>

        {{ __('main.total_users') }}: <b>{{ $total }}</b><br><br>
    @else

        {{ __('admin.delusers.deleted_condition') }} {{ formatTime($period) }}<br>
        {{ __('admin.delusers.asset_condition') }} {{ plural($point, setting('scorename')) }}<br><br>

        <b>{{ __('main.users') }}:</b>

        @foreach ($users as $user)
            <?php $comma = $loop->first ? '' : ',' ?>
            {{ $comma }} {!! $user->getProfile() !!}
        @endforeach

        <br><br>{{ __('admin.delusers.deleted_users') }}: <b>{{ $users->count() }}</b><br>

        <form action="/admin/delusers/clear" method="post">
            @csrf
            <input type="hidden" name="period" value="{{ $period }}">
            <input type="hidden" name="point" value="{{ $point }}">

            <button class="btn btn-primary">{{ __('admin.delusers.delete_users') }}</button>
        </form><br>
    @endif
@stop
