<div class="alert alert-danger" role="alert">
    @foreach ($errors as $error)
        <div><i class="fa fa-exclamation-circle fa-lg text-danger"></i> {{ $error }}</div>
    @endforeach
</div>
