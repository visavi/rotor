<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="far fa-circle fa-lg text-muted"></i>
        <a href="{{ route('news.index') }}" class="">{{ __('index.news') }}</a>
        <span class="badge bg-adaptive">{{ statsNewsDate() }}</span>
    </div>
    {{ pinnedNews() }}
</div>

<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="fa fa-comment fa-lg text-muted"></i>
        <a href="/pages/recent">{{ __('index.communication') }}</a>
    </div>
    <div class="section-body">
        <i class="far fa-circle fa-lg text-muted"></i> <a href="{{ route('guestbook.index') }}">{{ __('index.guestbook') }}</a> <span class="badge bg-adaptive">{{ statsGuestbook() }}</span><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="{{ route('photos.index') }}">{{ __('index.photos') }}</a> <span class="badge bg-adaptive">{{ statsPhotos() }}</span><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="{{ route('votes.index') }}">{{ __('index.votes') }}</a> <span class="badge bg-adaptive">{{ statVotes()}}</span><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="{{ route('offers.index') }}">{{ __('index.offers') }}</a> <span class="badge bg-adaptive">{{ statsOffers() }}</span><br>
    </div>
</div>

<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="fab fa-forumbee fa-lg text-muted"></i>
        <a href="{{ route('forums.index') }}">{{ __('index.forums') }}</a>
        <span class="badge bg-adaptive">{{ statsForum() }}</span>
    </div>
    {{ recentTopics() }}
</div>

<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="fa fa-download fa-lg text-muted"></i>
        <a href="{{ route('loads.index') }}">{{ __('index.loads') }}</a>
        <span class="badge bg-adaptive">{{ statsLoad() }}</span>
    </div>
    {{ recentDowns() }}
</div>

<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="fa fa-globe fa-lg text-muted"></i>
        <a href="{{ route('blogs.index') }}">{{ __('index.blogs') }}</a>
        <span class="badge bg-adaptive">{{ statsBlog() }}</span>
    </div>
    {{ recentArticles() }}
</div>

<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="fa fa-list-alt fa-lg text-muted"></i>
        <a href="{{ route('boards.index') }}">{{ __('index.boards') }}</a>
        <span class="badge bg-adaptive">{{ statsBoard() }}</span>
    </div>
    {{ recentBoards() }}
</div>

<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="fa fa-cog fa-lg text-muted"></i>
        <a href="/pages">{{ __('index.pages') }}</a>
    </div>
    <div class="section-body">
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/files/docs">{{ __('index.docs') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/search">{{ __('index.search') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/mails">{{ __('index.mails') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/users">{{ __('index.users') }}</a> <span class="badge bg-adaptive">{{ statsUsers() }}</span><br>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="section mb-3 shadow">
            <div class="section-title">
                <i class="fa fa-chart-line fa-lg text-muted"></i>
                {{ __('index.courses') }}
            </div>
            <div class="section-body">
                {{ getCourses() }}
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="section mb-3 shadow">
            <div class="section-title">
                <i class="fa fa-calendar-alt fa-lg text-muted"></i>
                {{ __('index.calendar') }}
            </div>
            <div class="section-body">
                {{ getCalendar() }}
            </div>
        </div>
    </div>
</div>

<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="fa fa-users fa-lg text-muted"></i>
        {{ __('main.who_online') }}
    </div>
    <div class="section-body">
        {{ onlineWidget() }}
    </div>
</div>
