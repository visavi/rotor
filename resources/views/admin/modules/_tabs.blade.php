<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link{{ request()->routeIs('admin.modules.index') ? ' active' : '' }}" href="{{ route('admin.modules.index') }}">
            {{ __('main.installed') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link{{ request()->routeIs('admin.modules.marketplace') ? ' active' : '' }}" href="{{ route('admin.modules.marketplace') }}">
            {{ __('admin.modules.marketplace') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link{{ request()->routeIs('admin.modules.upload') ? ' active' : '' }}" href="{{ route('admin.modules.upload') }}">
            {{ __('admin.modules.upload') }}
        </a>
    </li>
    <li class="nav-item ms-auto">
        <a class="nav-link{{ request()->routeIs('admin.registries.index') ? ' active' : '' }}" href="{{ route('admin.registries.index') }}">
            <i class="fas fa-database"></i> {{ __('admin.registries.title') }}
        </a>
    </li>
</ul>
