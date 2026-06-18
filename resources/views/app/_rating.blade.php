@php
    $vote = $vote ?? null;
    $type = $model->getMorphClass();
    $canVote = getUser() && getUser('id') !== $model->user_id;
@endphp
<span class="js-rating">
    @if ($canVote)
        <a class="post-rating-down{{ $vote === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $model->id }}" data-type="{{ $type }}" data-vote="-"><i class="fas fa-arrow-down"></i></a>
    @else
        <span class="post-rating-disabled"><i class="fas fa-arrow-down"></i></span>
    @endif
    <span class="rating-value">{{ formatNum($model->rating) }}</span>
    @if ($canVote)
        <a class="post-rating-up{{ $vote === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $model->id }}" data-type="{{ $type }}" data-vote="+"><i class="fas fa-arrow-up"></i></a>
    @else
        <span class="post-rating-disabled"><i class="fas fa-arrow-up"></i></span>
    @endif
</span>
