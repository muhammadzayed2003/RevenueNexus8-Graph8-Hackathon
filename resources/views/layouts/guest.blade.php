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

    <title>{{ config('app.name', 'RevenueNexus8') }}</title>

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
                href="{{ route('login') }}"
            >
                RevenueNexus<span>8</span><i></i>
            </a>

            <span class="rt-eyebrow">
                A new perspective on revenue
            </span>
        </header>
    </div>

    <main class="rt-auth rt-shell">
        <section class="rt-auth-art">
            <p class="rt-eyebrow">
                Your next decision, rehearsed.
            </p>

            <h1>
                Meet your<br>
                <em>other perspective.</em>
            </h1>

            <x-nexus-robot
                name="Revenue Nexus"
                size="large"
                :show-identity="false"
            />

            <p>
                Simulate the room. Explore the possibilities.<br>
                Move forward with a considered plan.
            </p>
        </section>

        <section class="rt-auth-form">
            <span class="rt-eyebrow">
                Your workspace
            </span>

            <h2>
                @if(request()->routeIs('register'))
                    Start thinking ahead.
                @elseif(request()->routeIs('login'))
                    Welcome back.
                @else
                    Account access
                @endif
            </h2>

            <div class="rt-auth-fields">
                {{ $slot }}
            </div>

            @if(request()->routeIs('login'))
                <p class="rt-auth-switch">
                    New here?

                    <a href="{{ route('register') }}">
                        Create an account â†—
                    </a>
                </p>
            @endif

            @if(request()->routeIs('register'))
                <p class="rt-auth-switch">
                    Already have an account?

                    <a href="{{ route('login') }}">
                        Sign in â†—
                    </a>
                </p>
            @endif
        </section>
    </main>
</body>
</html>

