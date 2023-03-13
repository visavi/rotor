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

@if ($message = session()->get('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <div>{{ $message }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($message = session()->get('danger'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div>{{ $message }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($message = session()->get('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <div>{{ $message }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($message = session()->get('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <div>{{ $message }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors = session()->get('errors'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
