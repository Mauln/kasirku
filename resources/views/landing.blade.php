<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-env" content="{{ config('app.deploy_env', 'local') }}">
    <meta name="app-version" content="{{ config('app.version', '0.1.0') }}">
    <title>Kasirku</title>
    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}?v={{ config('app.version', '0.1.0') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}?v={{ config('app.version', '0.1.0') }}">
    <link rel="stylesheet" href="{{ asset('css/utilities.css') }}?v={{ config('app.version', '0.1.0') }}">
</head>
<body>
    <main class="landing-shell">
        <section class="landing-card" aria-labelledby="kasirku-title">
            <h1 id="kasirku-title" class="landing-card__title">Kasirku — Coming Soon</h1>
            <p class="landing-card__subtitle">Kasir online UMKM Indonesia — cepat, murah, tanpa ribet.</p>
            <p class="landing-card__meta">
                Environment: <strong>{{ strtoupper((string) config('app.deploy_env', 'local')) }}</strong>
                &middot;
                <span id="kasirku-version-label">v{{ config('app.version', '0.1.0') }}</span>
            </p>
        </section>
    </main>

    <script type="module" src="{{ asset('js/app.js') }}?v={{ config('app.version', '0.1.0') }}"></script>
</body>
</html>
