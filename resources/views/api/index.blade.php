<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('index.api_interface') }}</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>
        body { margin: 0; }
        .swagger-ui .topbar { display: none; }
    </style>
</head>
<body>
<div id="swagger-ui"></div>
<script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
<script src="https://unpkg.com/js-yaml@4/dist/js-yaml.min.js"></script>
<script>
    fetch('{{ url('/openapi/openapi.yaml') }}')
        .then(r => r.text())
        .then(text => {
            const spec = jsyaml.load(text);
            spec.servers = [{ url: '{{ url('/api') }}' }];
            spec.info.contact = { name: '{{ setting('title') }}', url: '{{ url('/') }}' };
            SwaggerUIBundle({
                spec,
                dom_id: '#swagger-ui',
                presets: [SwaggerUIBundle.presets.apis, SwaggerUIBundle.SwaggerUIStandalonePreset],
                layout: 'BaseLayout',
                deepLinking: true,
                tryItOutEnabled: true,
                persistAuthorization: true,
            });
        });
</script>
</body>
</html>
