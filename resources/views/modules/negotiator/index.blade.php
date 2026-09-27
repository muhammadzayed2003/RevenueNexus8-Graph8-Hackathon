<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('dashboard') }}"
                   class="text-sm font-medium text-slate-500 hover:text-amber-600">
                    ← RevenueNexus8
                </a>

                <h2 class="mt-1 text-xl font-semibold text-slate-900">
                    Negotiator8
                </h2>
            </div>

            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                Dynamic Negotiation War-Game
            </span>
        </div>
    </x-slot>

    @php
        $result = $selectedSimulation?->result_data ?? [];
        $simulationRounds = $result['rounds'] ?? [];
        $risks = $result['key_risks'] ?? [];
        $concessions = $result['acceptable_concessions'] ?? [];
    @endphp

    <div class="min-h-screen bg-slate-950 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-sm font-medium text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->has('simulation'))
                <div class="mb-6 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-5 py-4 text-sm font-medium text-rose-300">
                    {{ $errors->first('simulation') }}
                </div>
            @endif

            <div class="grid gap-6 xl:grid-cols-[0.82fr_1.18fr]">

                <div class="space-y-6">
                    <section class="rounded-3xl border border-white/10 bg-slate-900 p-7">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-400">
                            Negotiation Setup
                        </p>

                        <h1 class="mt-3 text-3xl font-bold text-white">
                            Prepare the deal
                        </h1>

                        <p class="mt-3 text-sm leading-6 text-slate-400">
                            Gemini runs a multi-round war-game between dynamic buyer
                            and seller agents before a real response is sent.
                        </p>

                        <form
                            method="POST"
                            action="{{ route('negotiator.store') }}"
                            class="mt-7 space-y-5"
                            x-data="{ submitting: false }"
                            x-on:submit="submitting = true"
                        >
                            @csrf
                            <x-negotiation-context :deals="$deals" />


                            <div>
                                <label for="buyer" class="mb-2 block text-sm font-medium text-slate-300">
                                    Buyer or company
                                </label>

                                <input
                                    id="buyer"
                                    name="buyer"
                                    type="text"
                                    value="{{ old('buyer') }}"
                                    required
                                    placeholder="Example: Acme Technologies"
                                    class="w-full rounded-xl border-slate-700 bg-slate-950 text-white placeholder:text-slate-600 focus:border-amber-500 focus:ring-amber-500"
                                >

                                @error('buyer')
                                    <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label for="proposed_price" class="mb-2 block text-sm font-medium text-slate-300">
                                        Proposed price
                                    </label>

                                    <input
                                        id="proposed_price"
                                        name="proposed_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value="{{ old('proposed_price') }}"
                                        required
                                        placeholder="25000"
                                        class="w-full rounded-xl border-slate-700 bg-slate-950 text-white placeholder:text-slate-600 focus:border-amber-500 focus:ring-amber-500"
                                    >

                                    @error('proposed_price')
                                        <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="minimum_acceptable" class="mb-2 block text-sm font-medium text-slate-300">
                                        Minimum acceptable
                                    </label>

                                    <input
                                        id="minimum_acceptable"
                                        name="minimum_acceptable"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value="{{ old('minimum_acceptable') }}"
                                        required
                                        placeholder="20000"
                                        class="w-full rounded-xl border-slate-700 bg-slate-950 text-white placeholder:text-slate-600 focus:border-amber-500 focus:ring-amber-500"
                                    >

                                    @error('minimum_acceptable')
                                        <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="buyer_message" class="mb-2 block text-sm font-medium text-slate-300">
                                    Buyerâ€™s latest message or objection
                                </label>

                                <textarea
                                    id="buyer_message"
                                    name="buyer_message"
                                    rows="4"
                                    required
                                    placeholder="Paste the buyer's latest reply..."
                                    class="w-full rounded-xl border-slate-700 bg-slate-950 text-white placeholder:text-slate-600 focus:border-amber-500 focus:ring-amber-500"
                                >{{ old('buyer_message') }}</textarea>

                                @error('buyer_message')
                                    <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="objective" class="mb-2 block text-sm font-medium text-slate-300">
                                    Primary objective
                                </label>

                                <select
                                    id="objective"
                                    name="objective"
                                    required
                                    class="w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-amber-500 focus:ring-amber-500"
                                >
                                    @foreach ([
                                        'Protect deal value',
                                        'Close the deal quickly',
                                        'Handle price objection',
                                        'Improve payment terms',
                                        'Recover stalled negotiation',
                                    ] as $objective)
                                        <option
                                            value="{{ $objective }}"
                                            @selected(old('objective') === $objective)
                                        >
                                            {{ $objective }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('objective')
                                    <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="rounds" class="mb-2 block text-sm font-medium text-slate-300">
                                    Simulation rounds
                                </label>

                                <select
                                    id="rounds"
                                    name="rounds"
                                    required
                                    class="w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-amber-500 focus:ring-amber-500"
                                >
                                    @foreach ([3, 5, 7] as $roundCount)
                                        <option
                                            value="{{ $roundCount }}"
                                            @selected((int) old('rounds', 3) === $roundCount)
                                        >
                                            {{ $roundCount }} rounds
                                        </option>
                                    @endforeach
                                </select>

                                @error('rounds')
                                    <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                x-bind:disabled="submitting"
                                class="flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-5 py-3.5 font-semibold text-slate-950 transition hover:from-amber-400 hover:to-orange-500 disabled:cursor-wait disabled:opacity-60"
                            >
                                <span x-show="! submitting">
                                    Run Negotiation War-Game
                                </span>

                                <span x-show="submitting" x-cloak>
                                    Buyer and seller agents are negotiating...
                                </span>
                            </button>
                        </form>
                    </section>

                    <section class="rounded-3xl border border-white/10 bg-slate-900 p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="font-semibold text-white">
                                Negotiation History
                            </h2>

                            <span class="text-xs text-slate-500">
                                {{ $simulations->count() }} recent
                            </span>
                        </div>

                        <div class="mt-5 space-y-3">
                            @forelse ($simulations as $simulation)
                                <a
                                    href="{{ route('negotiator.index', ['simulation' => $simulation->id]) }}"
                                    class="block rounded-xl border p-4 transition
                                        {{ $selectedSimulation?->id === $simulation->id
                                            ? 'border-amber-500/50 bg-amber-500/10'
                                            : 'border-white/5 bg-slate-950 hover:border-amber-500/30' }}"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-medium text-white">
                                                {{ $simulation->title }}
                                            </p>

                                            <p class="mt-1 text-xs text-slate-500">
                                                {{ $simulation->created_at->diffForHumans() }}
                                            </p>
                                        </div>

                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                            {{ $simulation->status === 'completed'
                                                ? 'bg-emerald-500/10 text-emerald-300'
                                                : ($simulation->status === 'failed'
                                                    ? 'bg-rose-500/10 text-rose-300'
                                                    : 'bg-amber-500/10 text-amber-300') }}"
                                        >
                                            {{ ucfirst($simulation->status) }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <p class="rounded-xl border border-dashed border-slate-700 p-5 text-center text-sm text-slate-500">
                                    No negotiation simulations saved yet.
                                </p>
                            @endforelse
                        </div>
                    </section>
                </div>

                <section class="rounded-3xl border border-white/10 bg-slate-900 p-7">
                    @if ($selectedSimulation && $selectedSimulation->status === 'completed')
                        <x-agent-conversation :simulation="$selectedSimulation" />
                        <div class="flex flex-wrap items-start justify-between gap-5">
                            <div class="max-w-2xl">
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-400">
                                    War-Game Result
                                </p>

                                <h2 class="mt-3 text-2xl font-bold text-white">
                                    {{ $selectedSimulation->title }}
                                </h2>

                                <p class="mt-3 text-sm leading-6 text-slate-400">
                                    {{ $result['executive_summary'] ?? 'Negotiation completed.' }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-2xl bg-slate-950 px-5 py-4 text-center">
                                    <p class="text-xs uppercase tracking-wider text-slate-600">
                                        Win chance
                                    </p>

                                    <p class="mt-1 text-3xl font-bold text-emerald-400">
                                        {{ $result['win_probability'] ?? 0 }}%
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-950 px-5 py-4 text-center">
                                    <p class="text-xs uppercase tracking-wider text-slate-600">
                                        Best price
                                    </p>

                                    <p class="mt-1 text-2xl font-bold text-amber-400">
                                        ${{ number_format((float) ($result['recommended_price'] ?? 0), 0) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 rounded-2xl border border-amber-500/20 bg-amber-500/5 p-5">
                            <p class="text-xs uppercase tracking-wider text-amber-400">
                                Recommended Move
                            </p>

                            <p class="mt-3 text-lg font-semibold text-white">
                                {{ $result['recommended_move'] ?? 'Review negotiation result.' }}
                            </p>
                        </div>

                        <div class="mt-8">
                            <h3 class="font-semibold text-white">
                                Dynamic Negotiation Rounds
                            </h3>

                            <div class="mt-5 space-y-5">
                                @foreach ($simulationRounds as $round)
                                    <article class="overflow-hidden rounded-2xl border border-white/10 bg-slate-950">
                                        <div class="flex items-center justify-between border-b border-white/5 bg-slate-900 px-5 py-4">
                                            <p class="font-semibold text-white">
                                                Round {{ $round['round'] ?? $loop->iteration }}
                                            </p>

                                            <p class="text-sm font-semibold text-amber-300">
                                                ${{ number_format((float) ($round['offered_price'] ?? 0), 0) }}
                                            </p>
                                        </div>

                                        <div class="grid gap-4 p-5 md:grid-cols-2">
                                            <div class="rounded-xl border border-blue-500/20 bg-blue-500/5 p-4">
                                                <p class="text-xs font-semibold uppercase tracking-wider text-blue-300">
                                                    Buyer Move
                                                </p>

                                                <p class="mt-3 text-sm leading-6 text-slate-400">
                                                    {{ $round['buyer_move'] ?? '' }}
                                                </p>

                                                <div class="mt-4">
                                                    <div class="flex justify-between text-xs text-slate-500">
                                                        <span>Pressure</span>
                                                        <span>{{ $round['buyer_pressure'] ?? 0 }}%</span>
                                                    </div>

                                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800">
                                                        <div
                                                            class="h-full rounded-full bg-blue-500"
                                                            style="width: {{ min(100, max(0, $round['buyer_pressure'] ?? 0)) }}%"
                                                        ></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-4">
                                                <p class="text-xs font-semibold uppercase tracking-wider text-amber-300">
                                                    Seller Response
                                                </p>

                                                <p class="mt-3 text-sm leading-6 text-slate-400">
                                                    {{ $round['seller_response'] ?? '' }}
                                                </p>

                                                <div class="mt-4">
                                                    <div class="flex justify-between text-xs text-slate-500">
                                                        <span>Confidence</span>
                                                        <span>{{ $round['seller_confidence'] ?? 0 }}%</span>
                                                    </div>

                                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800">
                                                        <div
                                                            class="h-full rounded-full bg-amber-500"
                                                            style="width: {{ min(100, max(0, $round['seller_confidence'] ?? 0)) }}%"
                                                        ></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if (! empty($round['concession']))
                                            <div class="border-t border-white/5 px-5 py-4">
                                                <p class="text-xs text-slate-500">
                                                    Concession:
                                                    <span class="font-medium text-cyan-300">
                                                        {{ $round['concession'] }}
                                                    </span>
                                                </p>
                                            </div>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-8 grid gap-5 md:grid-cols-2">
                            <div class="rounded-2xl border border-rose-500/20 bg-rose-500/5 p-5">
                                <h3 class="font-semibold text-rose-300">
                                    Key Risks
                                </h3>

                                <ul class="mt-4 space-y-3">
                                    @forelse ($risks as $risk)
                                        <li class="flex gap-3 text-sm leading-6 text-slate-400">
                                            <span class="text-rose-400">•</span>
                                            <span>{{ $risk }}</span>
                                        </li>
                                    @empty
                                        <li class="text-sm text-slate-500">
                                            No major risks returned.
                                        </li>
                                    @endforelse
                                </ul>
                            </div>

                            <div class="rounded-2xl border border-cyan-500/20 bg-cyan-500/5 p-5">
                                <h3 class="font-semibold text-cyan-300">
                                    Acceptable Concessions
                                </h3>

                                <ul class="mt-4 space-y-3">
                                    @forelse ($concessions as $concession)
                                        <li class="flex gap-3 text-sm leading-6 text-slate-400">
                                            <span class="text-cyan-400">•</span>
                                            <span>{{ $concession }}</span>
                                        </li>
                                    @empty
                                        <li class="text-sm text-slate-500">
                                            No concessions recommended.
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        <div class="mt-8 rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-5">
                            <h3 class="font-semibold text-emerald-300">
                                Relay8 Recommendation
                            </h3>

                            <p class="mt-3 font-medium text-white">
                                {{ data_get($result, 'recommendation.title', 'Review recommendation') }}
                            </p>

                            <p class="mt-2 text-sm leading-6 text-slate-400">
                                {{ data_get($result, 'recommendation.summary', '') }}
                            </p>

                            <a
                                href="{{ route('relay.index') }}"
                                class="mt-5 inline-flex rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400"
                            >
                                Review in Relay8 →
                            </a>
                        </div>
                    @elseif ($selectedSimulation && $selectedSimulation->status === 'failed')
                        <div class="flex min-h-[650px] items-center justify-center text-center">
                            <div class="max-w-lg">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-500/10 text-2xl text-rose-400">
                                    !
                                </div>

                                <h2 class="mt-5 text-xl font-semibold text-white">
                                    Negotiation simulation failed
                                </h2>

                                <p class="mt-3 text-sm leading-6 text-rose-300">
                                    {{ $selectedSimulation->error_message }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="flex min-h-[650px] items-center justify-center text-center">
                            <div class="max-w-lg">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-500/10 text-3xl text-amber-400">
                                    â‡„
                                </div>

                                <h2 class="mt-5 text-xl font-semibold text-white">
                                    No negotiation selected
                                </h2>

                                <p class="mt-3 text-sm leading-6 text-slate-500">
                                    Enter a real buyer objection and commercial limits.
                                    Negotiator8 will dynamically generate every buyer and
                                    seller move, price and concession.
                                </p>
                            </div>
                        </div>
                    @endif
                </section>

            </div>
        </div>
    </div>
</x-app-layout>
