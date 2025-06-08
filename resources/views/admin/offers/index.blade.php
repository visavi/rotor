@extends('layout')

@section('title', __('index.offers'))

@section('header')
    <div class="float-end">
        <a class="btn btn-success" href="/offers/create?type={{ $type }}">{{ __('main.add') }}</a>
        <a class="btn btn-light" href="/offers/{{ $type }}?page={{ $offers->currentPage() }}"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ __('index.offers') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.offers') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        <div class="mb-3">
            <?php $active = ($type === 'offer') ? 'primary' : 'adaptive'; ?>
            <a class="btn btn-{{ $active }} btn-sm" href="/admin/offers/offer?sort={{ $sort }}&amp;order={{ $order }}">{{ __('offers.offers') }} <span class="badge bg-adaptive">{{ $offerCount }}</span></a>

            <?php $active = ($type === 'issue') ? 'primary' : 'adaptive'; ?>
            <a class="btn btn-{{ $active }} btn-sm" href="/admin/offers/issue?sort={{ $sort }}&amp;order={{ $order }}">{{ __('offers.problems') }} <span class="badge bg-adaptive">{{ $issueCount }}</span></a>
        </div>
    </div>

    @if ($offers->isNotEmpty())
        <div class="sort-links border-bottom pb-3 mb-3">
            {{ __('main.sort') }}:
            @foreach ($sorting as $key => $option)
                <a href="{{ route('admin.offers.index', ['type' => $type, 'sort' => $key, 'order' => $option['inverse'] ?? 'desc']) }}" class="badge bg-{{ $option['badge'] ?? 'adaptive' }}">
                    {{ $option['label'] }}{{ $option['icon'] ?? '' }}
                </a>
            @endforeach
        </div>

        <form action="/admin/offers/delete?type={{ $type }}&amp;page={{ $offers->currentPage() }}" method="post">
            @csrf
            @foreach ($offers as $data)
                <div class="section mb-3 shadow">
                    <div class="section-title">
                        <i class="fa fa-file"></i>
                        <a href="/admin/offers/{{ $data->id }}">{{ $data->title }}</a> ({{ __('main.votes') }}: {{ $data->rating }})
                        <div class="float-end">
                            <a href="/admin/offers/reply/{{ $data->id }}" data-bs-toggle="tooltip" title="{{ __('main.reply') }}"><i class="fas fa-reply text-muted"></i></a>
                            <a href="/admin/offers/edit/{{ $data->id }}" data-bs-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                            <input type="checkbox" class="form-check-input" name="del[]" value="{{ $data->id }}">
                        </div>
                    </div>

                    <div class="section-body">
                        {{ $data->getStatus() }}<br>
                        {{ bbCode($data->text) }}<br>
                        {{ __('main.added') }}: {{ $data->user->getProfile() }}
                        <small class="section-date text-muted fst-italic">{{ dateFixed($data->created_at) }}</small><br>
                        <a href="/offers/comments/{{ $data->id }}">{{ __('main.comments') }}</a> ({{ $data->count_comments }})
                        <a href="/offers/end/{{ $data->id }}">&raquo;</a>
                    </div>
                </div>
            @endforeach

            <div class="clearfix mb-3">
                <button class="btn btn-sm btn-danger float-end">{{ __('main.delete_selected') }}</button>
            </div>
        </form>

        {{ $offers->links() }}

        <div class="mb-3">
            {{ __('main.total') }}: <b>{{ $offers->total() }}</b>
        </div>
    @else
        {{ showError(__('main.empty_records')) }}
    @endif

    @if (isAdmin('boss'))
        <i class="fa fa-sync"></i> <a href="/admin/offers/restatement?_token={{ csrf_token() }}">{{ __('main.recount') }}</a><br>
    @endif
@stop
