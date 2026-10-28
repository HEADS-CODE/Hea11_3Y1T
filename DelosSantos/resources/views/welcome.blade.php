<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <main class="welcome-screen">
            <div class="welcome-card">
                <span class="eyebrow">Laravel + MySQL</span>
                <h1>Welcome to your task workspace.</h1>
                <p>Your starter project is ready. Open the task board to continue the activity.</p>
                <a class="button button-primary" href="{{ route('tasks.index') }}">Open task board <span aria-hidden="true">→</span></a>
            </div>
        </main>
    </body>
</html>
