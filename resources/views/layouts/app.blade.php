<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>{{ config('app.name', 'RevenueTwin8') }}</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body class="rt-app">
    <x-cinematic-intro />

    <div class="rt-shell">
        <header class="rt-topbar">
            <a
                class="rt-wordmark"
                href="{{ route('dashboard') }}"
            >
                RevenueTwin<span>8</span><i></i>
            </a>

            <div
                class="rt-account"
                x-data="{ open: false }"
                x-on:keydown.escape.window="open = false"
            >
                <span class="rt-eyebrow hidden sm:inline">
                    Revenue intelligence
                </span>

                <button
                    type="button"
                    class="rt-avatar"
                    x-on:click="open = !open"
                    x-bind:aria-expanded="open"
                    aria-label="Account menu"
                >
                    {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
                </button>

                <div
                    x-cloak
                    x-show="open"
                    x-transition
                    x-on:click.outside="open = false"
                    class="rt-menu"
                >
                    <strong>
                        {{ auth()->user()->name ?? 'User' }}
                    </strong>

                    <a href="{{ route('dashboard') }}">
                        Dashboard
                    </a>

                    <a href="{{ route('profile.edit') }}">
                        Profile
                    </a>

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <button type="submit">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </header>

        @isset($header)
            <div class="rt-page-heading">
                {{ $header }}
            </div>
        @endisset

        <main class="rt-content">
            {{ $slot }}
        </main>

        <footer class="rt-footer">
            <span>RevenueTwin8</span>

            <span>
                Think ahead. Act with confidence.
            </span>

            <a href="{{ route('relay.index') }}">
                Open Relay8 ↗
            </a>
        </footer>
    </div>
</body>
</html>