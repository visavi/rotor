@php
    $files ??= $model->files;
    $countFiles = $files->count();
@endphp

@if ($countFiles)
    @include('app/_slider')
@endif
