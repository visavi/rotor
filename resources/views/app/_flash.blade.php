@if (session()->has('flash'))
    @foreach (session('flash') as $status => $messages)
        <?php $messages = array_unique((array) $messages); ?>

        <div class="alert alert-{{ $status }} alert-dismissible fade show" role="alert">
            @foreach ($messages as $message)
                <div>{{ $message }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endforeach
    <?php session()->forget('flash'); ?>
@endif
