@if (isset($_SESSION['flash']))
    @foreach ($_SESSION['flash'] as $status => $messages)
        <?php $messages = array_unique((array) $messages); ?>
        <div class="alert alert-{{ $status }}" role="alert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            @foreach ($messages as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endforeach
    <?php unset($_SESSION['flash']); ?>
@endif
