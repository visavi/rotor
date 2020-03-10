@foreach ($messages as $message)
    <?php
        $author = $message->author_id ? $message->author->getName() : __('messages.system');
        $login = $message->author->exists ? $message->author->login : $message->author_id;
    ?>
    <li>
        <a class="app-notification__item" href="/messages/talk/{{ $login }}">
            <span class="app-notification__icon">{!! $message->author->getAvatarImage() !!}</span>
            <div>
                <p class="app-notification__author">{{ $author }}</p>
                <p class="app-notification__meta">{{ dateFixed($message->last_created_at) }}</p>
            </div>
        </a>
    </li>
@endforeach
