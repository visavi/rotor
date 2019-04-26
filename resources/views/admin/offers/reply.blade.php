@extends('layout')

@section('title')
    {{ trans('offers.reply_record') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/offers/{{ $offer->type }}">{{ trans('offers.title') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/offers/{{ $offer->id }}">{{ $offer->title }}</a></li>
            <li class="breadcrumb-item active">{{ trans('offers.reply_record') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/admin/offers/reply/{{ $offer->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('reply') }}">
                <label for="reply">{{ trans('offers.answer') }}:</label>
                <textarea class="form-control markItUp" id="reply" rows="5" name="reply" required>{{ getInput('reply', $offer->reply) }}</textarea>
                {!! textError('reply') !!}
            </div>

            <div class="form-group{{ hasError('status') }}">
                <label for="status">{{ trans('offers.status') }}:</label>

                <?php $inputStatus = getInput('status', $offer->status); ?>
                <select class="form-control" name="status" id="status">
                    @foreach ($statuses as $key => $status)
                        <?php $selected = ($key === $inputStatus) ? ' selected' : ''; ?>
                        <option value="{{ $key }}"{{ $selected }}>{{ $status }}</option>
                    @endforeach
                </select>

                {!! textError('status') !!}
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $offer->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">{{ trans('main.close_comments') }}</label>
            </div>

            <button class="btn btn-primary">{{ trans('main.reply') }}</button>
        </form>
    </div>
@stop
