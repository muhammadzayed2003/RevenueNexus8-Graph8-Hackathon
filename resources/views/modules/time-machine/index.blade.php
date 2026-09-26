<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('dashboard') }}"
                   class="text-sm font-medium text-slate-500 hover:text-cyan-600">
                    ← RevenueTwin8
                </a>

                <h2 class="mt-1 text-xl font-semibold text-slate-900">
                    TimeMachine8
                </h2>
            </div>

            <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-700">
                Historical Revenue Replay
            </span>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="rounded-3xl border border-cyan-500/20 bg-gradient-to-br from-cyan-950/50 via-slate-900 to-slate-950 p-8">
                <div class="grid gap-8 lg:grid-cols-[1fr_0.8fr] lg:items-center">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-400">
                            Strategy Backtesting
                        </p>

                        <h1 class="mt-3 text-4xl font-bold text-white">
                            Replay the past with a different strategy
                        </h1>

                        <p class="mt-4 max-w-2xl leading-7 text-slate-400">
                            Import historical graph8 events, select a candidate agent or
                            workflow, and calculate how the new strategy could have changed
                            engagement, conversion and revenue outcomes.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-400">graph8 connection</span>
                            <span class="rounded-full bg-amber-400/10 px-3 py-1 text-xs font-semibold text-amber-300">
                                Awaiting API
                            </span>
                        </div>

                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-800">
                            <div class="h-full w-1/4 rounded-full bg-cyan-500"></div>
                        </div>

                        <p class="mt-3 text-xs text-slate-500">
                            Live historical events will appear after graph8 credentials are connected.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">

                <section class="rounded-3xl border border-white/10 bg-slate-900 p-7">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-400">
                        Replay Configuration
                    </p>

                    <h2 class="mt-3 text-2xl font-bold text-white">
                        Configure simulation
                    </h2>

                    <form class="mt-6 space-y-5">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">
                                Historical period
                            </label>

                            <select class="w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-cyan-500 focus:ring-cyan-500">
                                <option>Last 30 days</option>
                                <option>Last 90 days</option>
                                <option>Last 6 months</option>
                                <option>Custom period</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">
                                Event type
                            </label>

                            <select class="w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-cyan-500 focus:ring-cyan-500">
                                <option>All revenue events</option>
                                <option>Email replies</option>
                                <option>Calls</option>
                                <option>Pipeline stage changes</option>
                                <option>Won and lost deals</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">
                                Candidate strategy
                            </label>

                            <textarea
                                rows="5"
                                placeholder="Describe the agent, workflow or strategy to test..."
                                class="w-full rounded-xl border-slate-700 bg-slate-950 text-white placeholder:text-slate-600 focus:border-cyan-500 focus:ring-cyan-500"
                            ></textarea>
                        </div>

                        <button
                            type="button"
                            class="w-full rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-5 py-3.5 font-semibold text-white transition hover:from-cyan-500 hover:to-blue-500"
                        >
                            Start Historical Replay
                        </button>
                    </form>
                </section>

                <section class="rounded-3xl border border-white/10 bg-slate-900 p-7">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                                Event Timeline
                            </p>

                            <h2 class="mt-3 text-2xl font-bold text-white">
                                Chronological replay
                            </h2>
                        </div>

                        <span class="rounded-full bg-slate-800 px-3 py-1 text-xs font-medium text-slate-400">
                            0 Events
                        </span>
                    </div>

                    <div class="mt-8 space-y-4">
                        <div class="flex gap-4 opacity-50">
                            <div class="flex flex-col items-center">
                                <div class="h-4 w-4 rounded-full border-4 border-slate-700 bg-slate-950"></div>
                                <div class="h-16 w-px bg-slate-800"></div>
                            </div>

                            <div class="flex-1 rounded-2xl border border-dashed border-slate-700 p-4">
                                <p class="text-sm font-semibold text-slate-400">Signal received</p>
                                <p class="mt-1 text-xs text-slate-600">Historical graph8 event</p>
                            </div>
                        </div>

                        <div class="flex gap-4 opacity-35">
                            <div class="flex flex-col items-center">
                                <div class="h-4 w-4 rounded-full border-4 border-slate-700 bg-slate-950"></div>
                                <div class="h-16 w-px bg-slate-800"></div>
                            </div>

                            <div class="flex-1 rounded-2xl border border-dashed border-slate-700 p-4">
                                <p class="text-sm font-semibold text-slate-400">Candidate action</p>
                                <p class="mt-1 text-xs text-slate-600">Alternative strategy response</p>
                            </div>
                        </div>

                        <div class="flex gap-4 opacity-20">
                            <div class="h-4 w-4 rounded-full border-4 border-slate-700 bg-slate-950"></div>

                            <div class="flex-1 rounded-2xl border border-dashed border-slate-700 p-4">
                                <p class="text-sm font-semibold text-slate-400">Outcome comparison</p>
                                <p class="mt-1 text-xs text-slate-600">Actual versus simulated result</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 rounded-2xl bg-slate-950 p-5 text-center">
                        <p class="font-semibold text-white">No replay running</p>
                        <p class="mt-2 text-sm text-slate-500">
                            Configure a historical period and candidate strategy to begin.
                        </p>
                    </div>
                </section>

            </div>
        </div>
    </div>
</x-app-layout>