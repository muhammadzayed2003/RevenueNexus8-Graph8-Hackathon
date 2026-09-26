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

            <div class="flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-semibold text-emerald-700">System Ready</span>
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

                    <div class="mt-8 flex flex-wrap gap-3">
                        <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                            <p class="text-xs uppercase tracking-wider text-slate-500">Data source</p>
                            <p class="mt-1 font-semibold text-white">graph8</p>
                        </div>

                        <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                            <p class="text-xs uppercase tracking-wider text-slate-500">Decision engines</p>
                            <p class="mt-1 font-semibold text-white">4 Modules</p>
                        </div>

                        <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                            <p class="text-xs uppercase tracking-wider text-slate-500">Execution mode</p>
                            <p class="mt-1 font-semibold text-emerald-400">Human Approved</p>
                        </div>
                    </div>
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
                        Select a module to begin
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2">

                    <a href="{{ route('boardroom.index') }}"
                       class="group relative overflow-hidden rounded-2xl border border-violet-500/20 bg-slate-900 p-6 transition duration-300 hover:-translate-y-1 hover:border-violet-400/60 hover:shadow-2xl hover:shadow-violet-950">
                        <div class="absolute right-0 top-0 h-32 w-32 rounded-full bg-violet-500/10 blur-3xl transition group-hover:bg-violet-500/20"></div>

                        <div class="relative">
                            <div class="flex items-start justify-between">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/15 text-2xl">
                                    ◈
                                </div>
                                <span class="rounded-full border border-violet-400/20 bg-violet-400/10 px-3 py-1 text-xs font-semibold text-violet-300">
                                    AI Committee
                                </span>
                            </div>

                            <h3 class="mt-6 text-2xl font-bold text-white">Boardroom8</h3>
                            <p class="mt-3 leading-6 text-slate-400">
                                Simulate a buyer committee where AI personas debate value,
                                risk, objections, budget and purchase readiness.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-white/5 pt-5">
                                <span class="text-sm text-slate-500">Buyer decision simulation</span>
                                <span class="text-violet-400 transition group-hover:translate-x-1">→</span>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('time-machine.index') }}"
                       class="group relative overflow-hidden rounded-2xl border border-cyan-500/20 bg-slate-900 p-6 transition duration-300 hover:-translate-y-1 hover:border-cyan-400/60 hover:shadow-2xl hover:shadow-cyan-950">
                        <div class="absolute right-0 top-0 h-32 w-32 rounded-full bg-cyan-500/10 blur-3xl transition group-hover:bg-cyan-500/20"></div>

                        <div class="relative">
                            <div class="flex items-start justify-between">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500/15 text-2xl">
                                    ◷
                                </div>
                                <span class="rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-semibold text-cyan-300">
                                    Historical Replay
                                </span>
                            </div>

                            <h3 class="mt-6 text-2xl font-bold text-white">TimeMachine8</h3>
                            <p class="mt-3 leading-6 text-slate-400">
                                Replay historical graph8 events and test how candidate
                                agents or workflows would perform against real timelines.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-white/5 pt-5">
                                <span class="text-sm text-slate-500">Strategy backtesting</span>
                                <span class="text-cyan-400 transition group-hover:translate-x-1">→</span>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('negotiator.index') }}"
                       class="group relative overflow-hidden rounded-2xl border border-amber-500/20 bg-slate-900 p-6 transition duration-300 hover:-translate-y-1 hover:border-amber-400/60 hover:shadow-2xl hover:shadow-amber-950">
                        <div class="absolute right-0 top-0 h-32 w-32 rounded-full bg-amber-500/10 blur-3xl transition group-hover:bg-amber-500/20"></div>

                        <div class="relative">
                            <div class="flex items-start justify-between">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/15 text-2xl">
                                    ⇄
                                </div>
                                <span class="rounded-full border border-amber-400/20 bg-amber-400/10 px-3 py-1 text-xs font-semibold text-amber-300">
                                    Negotiation Lab
                                </span>
                            </div>

                            <h3 class="mt-6 text-2xl font-bold text-white">Negotiator8</h3>
                            <p class="mt-3 leading-6 text-slate-400">
                                Run multi-round buyer and seller negotiation war-games
                                before using a pricing, objection or closing strategy.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-white/5 pt-5">
                                <span class="text-sm text-slate-500">Deal strategy simulation</span>
                                <span class="text-amber-400 transition group-hover:translate-x-1">→</span>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('relay.index') }}"
                       class="group relative overflow-hidden rounded-2xl border border-emerald-500/20 bg-slate-900 p-6 transition duration-300 hover:-translate-y-1 hover:border-emerald-400/60 hover:shadow-2xl hover:shadow-emerald-950">
                        <div class="absolute right-0 top-0 h-32 w-32 rounded-full bg-emerald-500/10 blur-3xl transition group-hover:bg-emerald-500/20"></div>

                        <div class="relative">
                            <div class="flex items-start justify-between">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/15 text-2xl">
                                    ⚡
                                </div>
                                <span class="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-300">
                                    Execution Gateway
                                </span>
                            </div>

                            <h3 class="mt-6 text-2xl font-bold text-white">Relay8</h3>
                            <p class="mt-3 leading-6 text-slate-400">
                                Review simulation recommendations, approve the strongest
                                decision and send the selected action back to graph8.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-white/5 pt-5">
                                <span class="text-sm text-slate-500">Approval and execution</span>
                                <span class="text-emerald-400 transition group-hover:translate-x-1">→</span>
                            </div>
                        </div>
                    </a>

                </div>
            </div>

            <div class="mt-10 rounded-2xl border border-white/10 bg-slate-900 p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                    Decision Flow
                </p>

                <div class="mt-5 grid gap-4 text-center text-sm md:grid-cols-7 md:items-center">
                    <div class="rounded-xl bg-slate-800 px-4 py-4 font-semibold text-white">graph8 Signals</div>
                    <div class="text-slate-600">→</div>
                    <div class="rounded-xl bg-violet-500/10 px-4 py-4 font-semibold text-violet-300">AI Simulation</div>
                    <div class="text-slate-600">→</div>
                    <div class="rounded-xl bg-amber-500/10 px-4 py-4 font-semibold text-amber-300">Human Approval</div>
                    <div class="text-slate-600">→</div>
                    <div class="rounded-xl bg-emerald-500/10 px-4 py-4 font-semibold text-emerald-300">Relay8 Execution</div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>