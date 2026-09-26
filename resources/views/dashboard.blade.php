<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">
                    RevenueTwin8
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    AI simulation and decision layer for graph8
                </p>
            </div>

            <div class="flex items-center gap-2 rounded-full px-3 py-1.5
                {{ $graph8Configured
                    ? 'bg-emerald-50'
                    : 'bg-amber-50' }}"
            >
                <span class="h-2 w-2 rounded-full
                    {{ $graph8Configured
                        ? 'bg-emerald-500'
                        : 'bg-amber-500' }}"
                ></span>

                <span class="text-xs font-semibold
                    {{ $graph8Configured
                        ? 'text-emerald-700'
                        : 'text-amber-700' }}"
                >
                    {{ $graph8Configured
                        ? 'graph8 Connected'
                        : 'graph8 Credentials Pending' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-indigo-950 via-slate-900 to-slate-950 p-8 shadow-2xl">
                <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-violet-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 left-1/3 h-64 w-64 rounded-full bg-cyan-500/10 blur-3xl"></div>

                <div class="relative">
                    <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-violet-400/20 bg-violet-400/10 px-3 py-1">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-violet-400"></span>

                        <span class="text-xs font-semibold uppercase tracking-widest text-violet-300">
                            Revenue Intelligence Command Center
                        </span>
                    </div>

                    <h1 class="max-w-3xl text-4xl font-bold leading-tight text-white md:text-5xl">
                        Simulate every revenue decision
                        <span class="bg-gradient-to-r from-violet-400 to-cyan-400 bg-clip-text text-transparent">
                            before execution.
                        </span>
                    </h1>

                    <p class="mt-5 max-w-2xl text-base leading-7 text-slate-300">
                        RevenueTwin8 reads graph8 revenue signals, runs AI simulations,
                        recommends the strongest action, and sends approved decisions
                        back to graph8 through Relay8.
                    </p>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-violet-500/20 bg-violet-500/5 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-violet-400">
                        Total Simulations
                    </p>

                    <p class="mt-3 text-3xl font-bold text-white">
                        {{ $simulationCount }}
                    </p>

                    <p class="mt-2 text-xs text-slate-500">
                        {{ $completedSimulationCount }} completed
                    </p>
                </div>

                <div class="rounded-2xl border border-cyan-500/20 bg-cyan-500/5 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-cyan-400">
                        Average Score
                    </p>

                    <p class="mt-3 text-3xl font-bold text-white">
                        {{ $averageScore !== null
                            ? number_format((float) $averageScore, 1)
                            : '—' }}
                    </p>

                    <p class="mt-2 text-xs text-slate-500">
                        Across completed simulations
                    </p>
                </div>

                <div class="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-400">
                        Pending Decisions
                    </p>

                    <p class="mt-3 text-3xl font-bold text-white">
                        {{ $pendingRecommendationCount }}
                    </p>

                    <p class="mt-2 text-xs text-slate-500">
                        Waiting in Relay8
                    </p>
                </div>

                <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-400">
                        graph8 Events
                    </p>

                    <p class="mt-3 text-3xl font-bold text-white">
                        {{ $graph8EventCount }}
                    </p>

                    <p class="mt-2 text-xs text-slate-500">
                        {{ $latestGraph8Event
                            ? 'Latest '.$latestGraph8Event->occurred_at?->diffForHumans()
                            : 'Waiting for live events' }}
                    </p>
                </div>
            </div>

            <div class="mt-10">
                <div class="mb-5 flex items-end justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-violet-400">
                            Simulation Suite
                        </p>

                        <h2 class="mt-2 text-2xl font-bold text-white">
                            RevenueTwin8 Modules
                        </h2>
                    </div>

                    <p class="hidden text-sm text-slate-500 md:block">
                        Live activity from your database
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <a
                        href="{{ route('boardroom.index') }}"
                        class="group relative overflow-hidden rounded-2xl border border-violet-500/20 bg-slate-900 p-6 transition duration-300 hover:-translate-y-1 hover:border-violet-400/60"
                    >
                        <div class="absolute right-0 top-0 h-32 w-32 rounded-full bg-violet-500/10 blur-3xl transition group-hover:bg-violet-500/20"></div>

                        <div class="relative">
                            <div class="flex items-start justify-between">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/15 text-2xl">
                                    ◈
                                </div>

                                <span class="rounded-full border border-violet-400/20 bg-violet-400/10 px-3 py-1 text-xs font-semibold text-violet-300">
                                    {{ $moduleCounts->get('boardroom8', 0) }} simulations
                                </span>
                            </div>

                            <h3 class="mt-6 text-2xl font-bold text-white">
                                Boardroom8
                            </h3>

                            <p class="mt-3 leading-6 text-slate-400">
                                Dynamic buyer committee personas debate value, risk,
                                budget, objections and purchase readiness.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-white/5 pt-5">
                                <span class="text-sm text-slate-500">
                                    Buyer decision simulation
                                </span>

                                <span class="text-violet-400 transition group-hover:translate-x-1">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>

                    <a
                        href="{{ route('time-machine.index') }}"
                        class="group relative overflow-hidden rounded-2xl border border-cyan-500/20 bg-slate-900 p-6 transition duration-300 hover:-translate-y-1 hover:border-cyan-400/60"
                    >
                        <div class="absolute right-0 top-0 h-32 w-32 rounded-full bg-cyan-500/10 blur-3xl transition group-hover:bg-cyan-500/20"></div>

                        <div class="relative">
                            <div class="flex items-start justify-between">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500/15 text-2xl">
                                    ◷
                                </div>

                                <span class="rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-semibold text-cyan-300">
                                    {{ $moduleCounts->get('time_machine8', 0) }} replays
                                </span>
                            </div>

                            <h3 class="mt-6 text-2xl font-bold text-white">
                                TimeMachine8
                            </h3>

                            <p class="mt-3 leading-6 text-slate-400">
                                Replays actual graph8 events and compares historical
                                outcomes with candidate strategies.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-white/5 pt-5">
                                <span class="text-sm text-slate-500">
                                    Strategy backtesting
                                </span>

                                <span class="text-cyan-400 transition group-hover:translate-x-1">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>

                    <a
                        href="{{ route('negotiator.index') }}"
                        class="group relative overflow-hidden rounded-2xl border border-amber-500/20 bg-slate-900 p-6 transition duration-300 hover:-translate-y-1 hover:border-amber-400/60"
                    >
                        <div class="absolute right-0 top-0 h-32 w-32 rounded-full bg-amber-500/10 blur-3xl transition group-hover:bg-amber-500/20"></div>

                        <div class="relative">
                            <div class="flex items-start justify-between">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/15 text-2xl">
                                    ⇄
                                </div>

                                <span class="rounded-full border border-amber-400/20 bg-amber-400/10 px-3 py-1 text-xs font-semibold text-amber-300">
                                    {{ $moduleCounts->get('negotiator8', 0) }} war-games
                                </span>
                            </div>

                            <h3 class="mt-6 text-2xl font-bold text-white">
                                Negotiator8
                            </h3>

                            <p class="mt-3 leading-6 text-slate-400">
                                Dynamic buyer and seller agents test multiple negotiation
                                rounds, prices and concessions.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-white/5 pt-5">
                                <span class="text-sm text-slate-500">
                                    Deal strategy simulation
                                </span>

                                <span class="text-amber-400 transition group-hover:translate-x-1">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>

                    <a
                        href="{{ route('relay.index') }}"
                        class="group relative overflow-hidden rounded-2xl border border-emerald-500/20 bg-slate-900 p-6 transition duration-300 hover:-translate-y-1 hover:border-emerald-400/60"
                    >
                        <div class="absolute right-0 top-0 h-32 w-32 rounded-full bg-emerald-500/10 blur-3xl transition group-hover:bg-emerald-500/20"></div>

                        <div class="relative">
                            <div class="flex items-start justify-between">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/15 text-2xl">
                                    ⚡
                                </div>

                                <span class="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-300">
                                    {{ $approvedRecommendationCount }} approved
                                </span>
                            </div>

                            <h3 class="mt-6 text-2xl font-bold text-white">
                                Relay8
                            </h3>

                            <p class="mt-3 leading-6 text-slate-400">
                                Reviews AI recommendations and sends approved payloads
                                to the actual graph8 execution API.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-white/5 pt-5">
                                <span class="text-sm text-slate-500">
                                    {{ $executedRecommendationCount }} actions executed
                                </span>

                                <span class="text-emerald-400 transition group-hover:translate-x-1">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <section class="rounded-2xl border border-white/10 bg-slate-900 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                                Recent Activity
                            </p>

                            <h2 class="mt-2 text-xl font-bold text-white">
                                Latest simulations
                            </h2>
                        </div>

                        <span class="text-xs text-slate-600">
                            Database Live
                        </span>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($recentSimulations as $simulation)
                            @php
                                $simulationUrl = match ($simulation->module) {
                                    'boardroom8' => route(
                                        'boardroom.index',
                                        ['simulation' => $simulation->id]
                                    ),
                                    'time_machine8' => route(
                                        'time-machine.index',
                                        ['simulation' => $simulation->id]
                                    ),
                                    'negotiator8' => route(
                                        'negotiator.index',
                                        ['simulation' => $simulation->id]
                                    ),
                                    default => route('dashboard'),
                                };
                            @endphp

                            <a
                                href="{{ $simulationUrl }}"
                                class="flex items-center justify-between gap-4 rounded-xl border border-white/5 bg-slate-950 p-4 transition hover:border-violet-500/30"
                            >
                                <div>
                                    <p class="font-medium text-white">
                                        {{ $simulation->title }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $simulation->module }}
                                        ·
                                        {{ $simulation->created_at->diffForHumans() }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="text-sm font-semibold text-violet-300">
                                        {{ $simulation->score !== null
                                            ? number_format((float) $simulation->score, 1)
                                            : '—' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-600">
                                        {{ ucfirst($simulation->status) }}
                                    </p>
                                </div>
                            </a>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-700 p-6 text-center text-sm text-slate-500">
                                No simulations have been created.
                            </p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-white/10 bg-slate-900 p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                        Live Decision Flow
                    </p>

                    <div class="mt-6 space-y-4">
                        <div class="rounded-xl bg-slate-950 p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-white">
                                    graph8 Signals
                                </span>

                                <span class="text-sm font-bold text-cyan-400">
                                    {{ $graph8EventCount }}
                                </span>
                            </div>
                        </div>

                        <div class="mx-auto h-5 w-px bg-slate-700"></div>

                        <div class="rounded-xl bg-violet-500/10 p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-violet-300">
                                    AI Simulations
                                </span>

                                <span class="text-sm font-bold text-violet-300">
                                    {{ $simulationCount }}
                                </span>
                            </div>
                        </div>

                        <div class="mx-auto h-5 w-px bg-slate-700"></div>

                        <div class="rounded-xl bg-amber-500/10 p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-amber-300">
                                    Pending Approval
                                </span>

                                <span class="text-sm font-bold text-amber-300">
                                    {{ $pendingRecommendationCount }}
                                </span>
                            </div>
                        </div>

                        <div class="mx-auto h-5 w-px bg-slate-700"></div>

                        <div class="rounded-xl bg-emerald-500/10 p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-emerald-300">
                                    graph8 Executions
                                </span>

                                <span class="text-sm font-bold text-emerald-300">
                                    {{ $executedRecommendationCount }}
                                </span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

        </div>
    </div>
</x-app-layout>