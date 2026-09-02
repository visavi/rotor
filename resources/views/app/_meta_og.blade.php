@php
    /**
     * Open Graph
     *
     * Значения берутся из секций страницы, у каждой есть запасной вариант.
     * Записи переопределяют секции image (картинка) и og_type (тип article)
     */
    $ogTitle = trim($__env->yieldContent('title')) ?: setting('title');
    $ogDescription = trim($__env->yieldContent('description')) ?: setting('description');
    $ogUrl = trim($__env->yieldContent('canonical')) ?: request()->url();
    $ogImage = trim($__env->yieldContent('image')) ?: '/assets/img/images/icon.png';
    $ogType = trim($__env->yieldContent('og_type')) ?: 'website';
@endphp
    <meta property="og:site_name" content="{{ setting('title') }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:url" content="{{ url($ogUrl) }}">
    <meta property="og:image" content="{{ url($ogImage) }}">
