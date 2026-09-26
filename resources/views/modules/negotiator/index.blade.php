<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('dashboard') }}"
                   class="text-sm font-medium text-slate-500 hover:text-amber-600">
                    ← RevenueTwin8
                </a>

                <h2 class="mt-1 text-xl font-semibold text-slate-900">
                    Negotiator8
                </h2>
            </div>

            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                Negotiation War-Game
            </span>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">

                <section class="rounded-3xl border border-white/10 bg-slate-900 p-7">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-400">
                        Negotiation Setup
                    </p>

                    <h1 class="mt-3 text-3xl font-bold text-white">
                        Prepare the deal
                    </h1>

                    <p class="mt-3 text-sm leading-6 text-slate-400">
                        Set the buyer position, seller limits and negotiation objective.
                        Negotiator8 will simulate multiple rounds before a real response is sent.
                    </p>

                    <form class="mt-7 space-y-5">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">
                                Buyer or company
                            </label>

                            <input
                                type="text"
                                placeholder="Example: Acme Technologies"
                                class="w-full rounded-xl border-slate-700 bg-slate-950 text-white placeholder:text-slate-600 focus:border-amber-500 focus:ring-amber-500"
                            >
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-300">
                                    Proposed price
                                </label>

                                <input
                                    type="number"
                                    placeholder="25000"
                                    class="w-full rounded-xl border-slate-700 bg-slate-950 text-white placeholder:text-slate-600 focus:border-amber-500 focus:ring-amber-500"
                                >
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-300">
                                    Minimum acceptable
                                </label>

                                <input
                                    type="number"
                                    placeholder="20000"
                                    class="w-full rounded-xl border-slate-700 bg-slate-950 text-white placeholder:text-slate-600 focus:border-amber-500 focus:ring-amber-500"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">
                                Buyer’s latest message or objection
                            </label>

                            <textarea
                                rows="4"
                                placeholder="Paste the buyer's latest reply..."
                                class="w-full rounded-xl border-slate-700 bg-slate-950 text-white placeholder:text-slate-600 focus:border-amber-500 focus:ring-amber-500"
                            ></textarea>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">
                                Primary objective
                            </label>

                            <select class="w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-amber-500 focus:ring-amber-500">
                                <option>Protect deal value</option>
                                <option>Close the deal quickly</option>
                                <option>Handle price objection</option>
                                <option>Improve payment terms</option>
                                <option>Recover stalled negotiation</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">
                                Simulation rounds
                            </label>

                            <select class="w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-amber-500 focus:ring-amber-500">
                                <option>3 rounds</option>
                                <option>5 rounds</option>
                                <option>7 rounds</option>
                            </select>
                        </div>

                        <button
                            type="button"
                            class="w-full rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-5 py-3.5 font-semibold text-slate-950 transition hover:from-amber-400 hover:to-orange-500"
                        >
                            Run Negotiation War-Game
                        </button>
                    </form>
                </section>

                <section class="rounded-3xl border border-white/10 bg-slate-900 p-7">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-400">
                                Simulation Arena
                            </p>

                            <h2 class="mt-3 text-2xl font-bold text-white">
                                Buyer versus Seller
                            </h2>
                        </div>

                        <div class="flex gap-2">
                            <span class="rounded-full bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-300">
                                Buyer AI
                            </span>

                            <span class="rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-300">
                                Seller AI
                            </span>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-blue-500/20 bg-blue-500/5 p-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-500/20 font-bold text-blue-300">
                                    B
                                </div>

                                <div>
                                    <p class="font-semibold text-white">Buyer Agent</p>
                                    <p class="text-xs text-slate-500">Protects budget and reduces risk</p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-3 text-sm text-slate-400">
                                <div class="rounded-xl bg-slate-950 p-3">Tests price resistance</div>
                                <div class="rounded-xl bg-slate-950 p-3">Raises commercial objections</div>
                                <div class="rounded-xl bg-slate-950 p-3">Evaluates seller concessions</div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500/20 font-bold text-amber-300">
                                    S
                                </div>

                                <div>
                                    <p class="font-semibold text-white">Seller Agent</p>
                                    <p class="text-xs text-slate-500">Protects value and advances the deal</p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-3 text-sm text-slate-400">
                                <div class="rounded-xl bg-slate-950 p-3">Defends solution value</div>
                                <div class="rounded-xl bg-slate-950 p-3">Trades instead of conceding</div>
                                <div class="rounded-xl bg-slate-950 p-3">Searches for close conditions</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-dashed border-slate-700 bg-slate-950/60 p-8 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-800 text-2xl text-amber-400">
                            ⇄
                        </div>

                        <h3 class="mt-4 font-semibold text-white">
                            Negotiation has not started
                        </h3>

                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                            After the simulation, each round will appear here with
                            buyer pressure, seller response, concessions and deal probability.
                        </p>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl bg-slate-950 p-4">
                            <p class="text-xs uppercase tracking-wider text-slate-600">Win probability</p>
                            <p class="mt-2 text-2xl font-bold text-slate-500">--%</p>
                        </div>

                        <div class="rounded-2xl bg-slate-950 p-4">
                            <p class="text-xs uppercase tracking-wider text-slate-600">Best price</p>
                            <p class="mt-2 text-2xl font-bold text-slate-500">--</p>
                        </div>

                        <div class="rounded-2xl bg-slate-950 p-4">
                            <p class="text-xs uppercase tracking-wider text-slate-600">Recommended move</p>
                            <p class="mt-2 text-sm font-semibold text-slate-500">Awaiting simulation</p>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>
</x-app-layout>