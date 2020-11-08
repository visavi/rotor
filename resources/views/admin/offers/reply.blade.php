@extends('layout')

@section('title', __('offers.reply_record'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/offers/{{ $offer->type }}">{{ __('index.offers') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/offers/{{ $offer->id }}">{{ $offer->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('offers.reply_record') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form shadow">
        <form action="/admin/offers/reply/{{ $offer->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('reply') }}">
                <label for="reply">{{ __('offers.answer') }}:</label>
                <textarea class="form-control markItUp" id="reply" rows="5" name="reply" required>{{ getInput('reply', $offer->reply) }}</textarea>
                <div class="invalid-feedback">{{ textError('reply') }}</div>
            </div>

            <div class="form-group{{ hasError('status') }}">
                <label for="status">{{ __('offers.status') }}:</label>

                <?php $inputStatus = getInput('status', $offer->status); ?>
                <select class="form-control" name="status" id="status">
                    @foreach ($statuses as $status)
                        <?php $selected = ($status === $inputStatus) ? ' selected' : ''; ?>
                        <option value="{{ $status }}"{{ $selected }}>{{ __('offers.' . $status) }}</option>
                    @endforeach
                </select>

                <div class="invalid-feedback">{{ textError('status') }}</div>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $offer->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">{{ __('main.close_comments') }}</label>
            </div>

            <button class="btn btn-primary">{{ __('main.reply') }}</button>
        </form>
    </div>
@stop
