@php
    $vote = $vote ?? null;
    $type = $model->getMorphClass();
    $canVote = getUser() && getUser('id') !== $model->user_id;
@endphp
{{-- После голоса сервер отдаёт этот же блок целиком, клиент подменяет его по .js-rating --}}
<span class="js-rating">
    @if ($canVote)
        <a class="post-rating-down{{ $vote === '-' ? ' active' : '' }}" href="#" data-ajax data-ajax-url="/ajax/rating" data-ajax-replace=".js-rating" data-ajax-swap="outer" data-id="{{ $model->id }}" data-type="{{ $type }}" data-vote="-"><i class="fas fa-arrow-down"></i></a>
    @else
        <span class="post-rating-disabled"><i class="fas fa-arrow-down"></i></span>
    @endif
    <span class="rating-value">{{ formatNum($model->rating ?? 0) }}</span>
    @if ($canVote)
        <a class="post-rating-up{{ $vote === '+' ? ' active' : '' }}" href="#" data-ajax data-ajax-url="/ajax/rating" data-ajax-replace=".js-rating" data-ajax-swap="outer" data-id="{{ $model->id }}" data-type="{{ $type }}" data-vote="+"><i class="fas fa-arrow-up"></i></a>
    @else
        <span class="post-rating-disabled"><i class="fas fa-arrow-up"></i></span>
    @endif
</span>
