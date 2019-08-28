@if (isset($_SESSION['flash']))



    @foreach ($_SESSION['flash'] as $status => $messages)
        @if (is_array($messages))
            <?php $messages = implode('</div><div>', array_unique($messages)); ?>
        @endif
        <div class="alert alert-{{ $status }} alert-block">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <div>{!! $messages !!}</div>
        </div>

    @endforeach
    <?php unset($_SESSION['flash']); ?>
@endif
