@if (isset($_SESSION['flash']))
    @foreach ($_SESSION['flash'] as $status => $messages)
        <?php $messages = array_unique((array) $messages); ?>

        <div class="alert alert-{{ $status }} alert-dismissible fade show" role="alert">
            @foreach ($messages as $message)
                <div>{{ $message }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endforeach
    <?php unset($_SESSION['flash']); ?>
@endif
