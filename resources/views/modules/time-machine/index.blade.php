<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('dashboard') }}"
                   class="text-sm font-medium text-slate-500 hover:text-cyan-600">
                    ← RevenueNexus8
                </a>

                <h2 class="mt-1 text-xl font-semibold text-slate-900">
                    TimeMachine8
                </h2>
            </div>

            <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-700">
                Dynamic Historical Replay
            </span>
        </div>
    </x-slot>

    @php
        $result = $selectedSimulation?->result_data ?? [];
        $timeline = $result['timeline'] ?? [];
        $findings = $result['key_findings'] ?? [];
        $limitations = $result['limitations'] ?? [];
    @endphp

    <div class="min-h-screen bg-slate-950 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-sm font-medium text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->has('events'))
                <div class="mb-6 rounded-2xl border border-amber-500/30 bg-amber-500/10 px-5 py-4 text-sm font-medium text-amber-300">
                    {{ $errors->first('events') }}
                </div>
            @endif

            @if ($errors->has('simulation'))
                <div class="mb-6 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-5 py-4 text-sm font-medium text-rose-300">
                    {{ $errors->first('simulation') }}
                </div>
            @endif

            <div class="rounded-3xl border border-cyan-500/20 bg-gradient-to-br from-cyan-950/50 via-slate-900 to-slate-950 p-8">
                <div class="flex flex-wrap items-center justify-between gap-8">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-400">
                            graph8 Historical Feed
                        </p>

                        <h1 class="mt-3 text-4xl font-bold text-white">
                            Replay the past with a new strategy
                        </h1>

                        <p class="mt-4 max-w-2xl leading-7 text-slate-400">
                            TimeMachine8 reads stored graph8 webhook events in chronological
                            order and tests how a candidate workflow could change the outcome.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-2xl border border-cyan-500/20 bg-cyan-500/5 px-6 py-4 text-center">
                            <p class="text-xs uppercase tracking-wider text-cyan-400">
                                Events
                            </p>

                            <p class="mt-2 text-3xl font-bold text-white">
                                {{ $eventCount }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/5 px-6 py-4 text-center">
                            <p class="text-xs uppercase tracking-wider text-emerald-400">
                                Webhook
                            </p>

                            <p class="mt-2 text-sm font-bold text-emerald-300">
                                Connected
                            </p>
                        </div>
                    </div>
                </div>

                @if ($latestEvent)
                    <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-wider text-slate-500">
                                    Latest graph8 event
                                </p>

                                <p class="mt-1 font-semibold text-white">
                                    {{ $latestEvent->event_type }}
                                </p>
                            </div>

                            <p class="text-xs text-slate-500">
                                {{ $latestEvent->occurred_at?->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-[0.82fr_1.18fr]">

                <div class="space-y-6">
                    <section class="rounded-3xl border border-white/10 bg-slate-900 p-7">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-400">
                            Replay Configuration
                        </p>

                        <h2 class="mt-3 text-2xl font-bold text-white">
                            Configure simulation
                        </h2>

                        <form
                            method="POST"
                            action="{{ route('time-machine.store') }}"
                            class="mt-6 space-y-5"
                            x-data="{ submitting: false }"
                            x-on:submit="submitting = true"
                        >
                            @csrf

                            <div>
                                <label for="period_days" class="mb-2 block text-sm font-medium text-slate-300">
                                    Historical period
                                </label>

                                <select
                                    id="period_days"
                                    name="period_days"
                                    required
                                    class="w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-cyan-500 focus:ring-cyan-500"
                                >
                                    @foreach ([
                                        30 => 'Last 30 days',
                                        90 => 'Last 90 days',
                                        180 => 'Last 6 months',
                                        365 => 'Last 12 months',
                                    ] as $days => $label)
                                        <option
                                            value="{{ $days }}"
                                            @selected((int) old('period_days', 30) === $days)
                                        >
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('period_days')
                                    <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="event_type" class="mb-2 block text-sm font-medium text-slate-300">
                                    graph8 event type
                                </label>

                                <select
                                    id="event_type"
                                    name="event_type"
                                    required
                                    class="w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-cyan-500 focus:ring-cyan-500"
                                >
                                    <option value="all">All revenue events</option>

                                    @foreach ($availableEventTypes as $eventType)
                                        <option
                                            value="{{ $eventType }}"
                                            @selected(old('event_type') === $eventType)
                                        >
                                            {{ $eventType }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('event_type')
                                    <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="candidate_strategy" class="mb-2 block text-sm font-medium text-slate-300">
                                    Candidate strategy
                                </label>

                                <textarea
                                    id="candidate_strategy"
                                    name="candidate_strategy"
                                    rows="7"
                                    required
                                    placeholder="Describe the agent, sequence, workflow or response strategy to test against the historical events..."
                                    class="w-full rounded-xl border-slate-700 bg-slate-950 text-white placeholder:text-slate-600 focus:border-cyan-500 focus:ring-cyan-500"
                                >{{ old('candidate_strategy') }}</textarea>

                                @error('candidate_strategy')
                                    <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                x-bind:disabled="submitting || {{ $eventCount }} === 0"
                                class="flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-5 py-3.5 font-semibold text-white transition hover:from-cyan-500 hover:to-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span x-show="! submitting">
                                    Start Historical Replay
                                </span>

                                <span x-show="submitting" x-cloak>
                                    Replaying graph8 events...
                                </span>
                            </button>
                        </form>
                    </section>

                    <section class="rounded-3xl border border-white/10 bg-slate-900 p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="font-semibold text-white">
                                Replay History
                            </h2>

                            <span class="text-xs text-slate-500">
                                {{ $simulations->count() }} recent
                            </span>
                        </div>

                        <div class="mt-5 space-y-3">
                            @forelse ($simulations as $simulation)
                                <a
                                    href="{{ route('time-machine.index', ['simulation' => $simulation->id]) }}"
                                    class="block rounded-xl border p-4 transition
                                        {{ $selectedSimulation?->id === $simulation->id
                                            ? 'border-cyan-500/50 bg-cyan-500/10'
                                            : 'border-white/5 bg-slate-950 hover:border-cyan-500/30' }}"
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
                                    No historical replays saved yet.
                                </p>
                            @endforelse
                        </div>
                    </section>
                </div>

                <section class="rounded-3xl border border-white/10 bg-slate-900 p-7">
                    @if ($selectedSimulation && $selectedSimulation->status === 'completed')
                        <div class="flex flex-wrap items-start justify-between gap-5">
                            <div class="max-w-2xl">
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-400">
                                    Replay Result
                                </p>

                                <h2 class="mt-3 text-2xl font-bold text-white">
                                    {{ $selectedSimulation->title }}
                                </h2>

                                <p class="mt-3 text-sm leading-6 text-slate-400">
                                    {{ $result['executive_summary'] ?? 'Historical replay completed.' }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-2xl bg-slate-950 px-5 py-4 text-center">
                                    <p class="text-xs uppercase tracking-wider text-slate-600">
                                        Projected uplift
                                    </p>

                                    <p class="mt-1 text-3xl font-bold text-emerald-400">
                                        {{ $result['projected_uplift_percent'] ?? 0 }}%
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-950 px-5 py-4 text-center">
                                    <p class="text-xs uppercase tracking-wider text-slate-600">
                                        Confidence
                                    </p>

                                    <p class="mt-1 text-3xl font-bold text-cyan-400">
                                        {{ $result['confidence_score'] ?? 0 }}%
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 grid gap-5 md:grid-cols-2">
                            <div class="rounded-2xl border border-slate-700 bg-slate-950 p-5">
                                <p class="text-xs uppercase tracking-wider text-slate-600">
                                    Actual Path
                                </p>

                                <p class="mt-3 text-sm leading-6 text-slate-400">
                                    {{ $result['actual_outcome'] ?? '' }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-cyan-500/20 bg-cyan-500/5 p-5">
                                <p class="text-xs uppercase tracking-wider text-cyan-400">
                                    Simulated Path
                                </p>

                                <p class="mt-3 text-sm leading-6 text-slate-300">
                                    {{ $result['simulated_outcome'] ?? '' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-8">
                            <h3 class="font-semibold text-white">
                                Chronological Event Replay
                            </h3>

                            <div class="mt-5 space-y-4">
                                @foreach ($timeline as $item)
                                    <article class="rounded-2xl border border-white/10 bg-slate-950 p-5">
                                        <div class="flex flex-wrap items-start justify-between gap-4">
                                            <div>
                                                <p class="text-sm font-semibold text-cyan-300">
                                                    {{ $item['event_type'] ?? 'graph8 event' }}
                                                </p>

                                                <p class="mt-1 text-xs text-slate-600">
                                                    {{ $item['occurred_at'] ?? '' }}
                                                </p>
                                            </div>

                                            <span class="rounded-full bg-slate-800 px-3 py-1 text-xs text-slate-400">
                                                {{ $item['event_id'] ?? 'Event' }}
                                            </span>
                                        </div>

                                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                                            <div class="rounded-xl bg-slate-900 p-4">
                                                <p class="text-xs uppercase tracking-wider text-slate-600">
                                                    Actual action
                                                </p>

                                                <p class="mt-2 text-sm leading-6 text-slate-400">
                                                    {{ $item['actual_action'] ?? '' }}
                                                </p>
                                            </div>

                                            <div class="rounded-xl border border-cyan-500/20 bg-cyan-500/5 p-4">
                                                <p class="text-xs uppercase tracking-wider text-cyan-400">
                                                    Simulated action
                                                </p>

                                                <p class="mt-2 text-sm leading-6 text-slate-300">
                                                    {{ $item['simulated_action'] ?? '' }}
                                                </p>
                                            </div>
                                        </div>

                                        <p class="mt-4 text-sm text-emerald-300">
                                            Impact: {{ $item['impact'] ?? '' }}
                                        </p>
                                    </article>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-8 grid gap-5 md:grid-cols-2">
                            <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-5">
                                <h3 class="font-semibold text-emerald-300">
                                    Key Findings
                                </h3>

                                <ul class="mt-4 space-y-3">
                                    @forelse ($findings as $finding)
                                        <li class="flex gap-3 text-sm leading-6 text-slate-400">
                                            <span class="text-emerald-400">•</span>
                                            <span>{{ $finding }}</span>
                                        </li>
                                    @empty
                                        <li class="text-sm text-slate-500">
                                            No findings returned.
                                        </li>
                                    @endforelse
                                </ul>
                            </div>

                            <div class="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-5">
                                <h3 class="font-semibold text-amber-300">
                                    Evidence Limitations
                                </h3>

                                <ul class="mt-4 space-y-3">
                                    @forelse ($limitations as $limitation)
                                        <li class="flex gap-3 text-sm leading-6 text-slate-400">
                                            <span class="text-amber-400">•</span>
                                            <span>{{ $limitation }}</span>
                                        </li>
                                    @empty
                                        <li class="text-sm text-slate-500">
                                            No limitations returned.
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
                                    Historical replay failed
                                </h2>

                                <p class="mt-3 text-sm leading-6 text-rose-300">
                                    {{ $selectedSimulation->error_message }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="flex min-h-[650px] items-center justify-center text-center">
                            <div class="max-w-lg">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-cyan-500/10 text-3xl text-cyan-400">
                                    â—·
                                </div>

                                <h2 class="mt-5 text-xl font-semibold text-white">
                                    No replay selected
                                </h2>

                                <p class="mt-3 text-sm leading-6 text-slate-500">
                                    TimeMachine8 uses dynamically received graph8 events.
                                    Select a period and describe a candidate strategy to
                                    compare the actual and simulated revenue paths.
                                </p>
                            </div>
                        </div>
                    @endif
                </section>

            </div>
        </div>
    </div>
</x-app-layout>
