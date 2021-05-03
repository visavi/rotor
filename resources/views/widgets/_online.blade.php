{{ __('main.guests_online', ['count' => $online[1]]) }}

@if ($online[3]->isNotEmpty())
    {{ __('main.and') }}
    @foreach ($online[3] as $key => $user)
        {{ $loop->first ? '' : ',' }} {{ $user->user->getProfile() }}
    @endforeach
@endif
