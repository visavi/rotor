<script src="/assets/modules/{{ setting('language') }}.js"></script>
@if (file_exists(HOME . '/assets/js/compiled.js'))
    <script src="/assets/js/compiled.js"></script>
@else
    <script src="/assets/js/jquery-3.3.1.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bootstrap-colorpicker.min.js"></script>
    <script src="/assets/js/prettify.js"></script>
    <script src="/assets/js/bootbox.min.js"></script>
    <script src="/assets/js/toastr.min.js"></script>
    <script src="/assets/js/markitup/jquery.markitup.js"></script>
    <script src="/assets/js/markitup/markitup.set.js"></script>
    <script src="/assets/js/mediaelement/mediaelement-and-player.min.js"></script>
    <script src="/assets/js/colorbox/jquery.colorbox-min.js"></script>
    <script src="/assets/js/jquery.mask.min.js"></script>
    <script src="/assets/js/app.js"></script>
@endif
