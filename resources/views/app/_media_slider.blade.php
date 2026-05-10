@php
    $files ??= $model->files;
    $countFiles = $files->count();
@endphp

@if ($countFiles)
    @include('app/_slider')
    {{--@include('app/_carousel')--}}
@endif
