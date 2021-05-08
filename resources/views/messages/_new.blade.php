@foreach ($dialogues as $dialogue)
    <?php
        $author = $dialogue->author_id ? $dialogue->author->getName() : __('messages.system');
        $login = $dialogue->author->exists ? $dialogue->author->login : $dialogue->author_id;
    ?>
    <li>
        <a class="app-notification__item" href="/messages/talk/{{ $login }}">
            <span class="app-notification__icon avatar-mini">{{ $dialogue->author->getAvatarImage() }}</span>
            <div>
                <p class="app-notification__author">{{ $author }}</p>
                <p class="app-notification__meta">{{ dateFixed($dialogue->last_created_at) }}</p>
            </div>

            <div class="app-notification__cnt">
                <span class="badge bg-info">{{ $dialogue->cnt }}</span>
            </div>
        </a>
    </li>
@endforeach
