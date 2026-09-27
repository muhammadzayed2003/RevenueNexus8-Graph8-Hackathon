<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('dashboard') }}"
                   class="text-sm font-medium text-slate-500 hover:text-violet-600">
                    ← RevenueNexus8
                </a>

                <h2 class="mt-1 text-xl font-semibold text-slate-900">
                    Boardroom8
                </h2>
            </div>

            <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700">
                graph8 Buyer Committee
            </span>
        </div>
    </x-slot>

    @php
        $result = $selectedSimulation?->result_data ?? [];
        $personas = $result['personas'] ?? [];
        $debate = $result['debate'] ?? [];
        $objections = $result['key_objections'] ?? [];
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

            <div class="mb-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-violet-500/20 bg-violet-500/5 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-violet-400">
                        Synced graph8 Companies
                    </p>

                    <p class="mt-2 text-3xl font-bold text-white">
                        {{ $companies->count() }}
                    </p>
                </div>

                <div class="rounded-2xl border border-cyan-500/20 bg-cyan-500/5 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-cyan-400">
                        Synced graph8 Deals
                    </p>

                    <p class="mt-2 text-3xl font-bold text-white">
                        {{ $deals->count() }}
                    </p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[0.82fr_1.18fr]">

                <div class="space-y-6">
                    <section class="rounded-3xl border border-white/10 bg-slate-900 p-7">
                        <div class="mb-7">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-violet-400">
                                New Simulation
                            </p>

                            <h1 class="mt-3 text-3xl font-bold text-white">
                                Select graph8 revenue data
                            </h1>

                            <p class="mt-3 text-sm leading-6 text-slate-400">
                                Company and deal context comes from the actual graph8 API.
                                Gemini uses that data to simulate the buyer committee.
                            </p>
                        </div>

                        @if ($companies->isEmpty())
                            <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-5 text-sm text-amber-300">
                                No graph8 companies are synced. Run
                                <code class="font-semibold">php artisan graph8:sync</code>
                                before starting a simulation.
                            </div>
                        @else
                            <form
                                method="POST"
                                action="{{ route('boardroom.store') }}"
                                class="space-y-5"
                                x-data="{ submitting: false }"
                                x-on:submit="submitting = true"
                            >
                                @csrf

                                <div>
                                    <label for="company_record_id" class="mb-2 block text-sm font-medium text-slate-300">
                                        graph8 Company
                                    </label>

                                    <select
                                        id="company_record_id"
                                        name="company_record_id"
                                        required
                                        class="w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-violet-500 focus:ring-violet-500"
                                    >
                                        <option value="">Select a synced company</option>

                                        @foreach ($companies as $company)
                                            <option
                                                value="{{ $company->id }}"
                                                @selected((int) old('company_record_id') === $company->id)
                                            >
                                                {{ $company->name ?? 'Company '.$company->external_id }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('company_record_id')
                                        <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="deal_record_id" class="mb-2 block text-sm font-medium text-slate-300">
                                        graph8 Deal
                                        <span class="text-slate-600">(optional)</span>
                                    </label>

                                    <select
                                        id="deal_record_id"
                                        name="deal_record_id"
                                        class="w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-violet-500 focus:ring-violet-500"
                                        @disabled($deals->isEmpty())
                                    >
                                        <option value="">
                                            {{ $deals->isEmpty()
                                                ? 'No graph8 deals available'
                                                : 'Select a synced deal' }}
                                        </option>

                                        @foreach ($deals as $deal)
                                            <option
                                                value="{{ $deal->id }}"
                                                @selected((int) old('deal_record_id') === $deal->id)
                                            >
                                                {{ $deal->name ?? 'Deal '.$deal->external_id }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('deal_record_id')
                                        <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid gap-5 md:grid-cols-2">
                                    <div>
                                        <label for="deal_value" class="mb-2 block text-sm font-medium text-slate-300">
                                            Deal value
                                        </label>

                                        <input
                                            id="deal_value"
                                            name="deal_value"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            value="{{ old('deal_value') }}"
                                            required
                                            placeholder="25000"
                                            class="w-full rounded-xl border-slate-700 bg-slate-950 text-white placeholder:text-slate-600 focus:border-violet-500 focus:ring-violet-500"
                                        >

                                        @error('deal_value')
                                            <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="stage" class="mb-2 block text-sm font-medium text-slate-300">
                                            Current stage
                                        </label>

                                        <select
                                            id="stage"
                                            name="stage"
                                            required
                                            class="w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-violet-500 focus:ring-violet-500"
                                        >
                                            @foreach ([
                                                'Lead',
                                                'Qualified',
                                                'Meeting',
                                                'Proposal',
                                                'Negotiation',
                                                'Closed Won',
                                                'Closed Lost',
                                            ] as $stage)
                                                <option
                                                    value="{{ $stage }}"
                                                    @selected(old('stage') === $stage)
                                                >
                                                    {{ $stage }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('stage')
                                            <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="solution" class="mb-2 block text-sm font-medium text-slate-300">
                                        Proposed solution
                                    </label>

                                    <textarea
                                        id="solution"
                                        name="solution"
                                        rows="4"
                                        required
                                        placeholder="Describe the offer being evaluated..."
                                        class="w-full rounded-xl border-slate-700 bg-slate-950 text-white placeholder:text-slate-600 focus:border-violet-500 focus:ring-violet-500"
                                    >{{ old('solution') }}</textarea>

                                    @error('solution')
                                        <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="objections" class="mb-2 block text-sm font-medium text-slate-300">
                                        Known objections
                                    </label>

                                    <textarea
                                        id="objections"
                                        name="objections"
                                        rows="3"
                                        placeholder="Budget, security, competitor, timing..."
                                        class="w-full rounded-xl border-slate-700 bg-slate-950 text-white placeholder:text-slate-600 focus:border-violet-500 focus:ring-violet-500"
                                    >{{ old('objections') }}</textarea>

                                    @error('objections')
                                        <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button
                                    type="submit"
                                    x-bind:disabled="submitting"
                                    class="flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-3.5 font-semibold text-white transition hover:from-violet-500 hover:to-indigo-500 disabled:cursor-wait disabled:opacity-60"
                                >
                                    <span x-show="! submitting">
                                        Run Boardroom Simulation
                                    </span>

                                    <span x-show="submitting" x-cloak>
                                        Simulating graph8 company committee...
                                    </span>
                                </button>
                            </form>
                        @endif
                    </section>

                    <section class="rounded-3xl border border-white/10 bg-slate-900 p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="font-semibold text-white">
                                Simulation History
                            </h2>

                            <span class="text-xs text-slate-500">
                                {{ $simulations->count() }} recent
                            </span>
                        </div>

                        <div class="mt-5 space-y-3">
                            @forelse ($simulations as $simulation)
                                <a
                                    href="{{ route('boardroom.index', ['simulation' => $simulation->id]) }}"
                                    class="block rounded-xl border p-4 transition
                                        {{ $selectedSimulation?->id === $simulation->id
                                            ? 'border-violet-500/50 bg-violet-500/10'
                                            : 'border-white/5 bg-slate-950 hover:border-violet-500/30' }}"
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
                                    No simulations saved yet.
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
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-violet-400">
                                    Simulation Result
                                </p>

                                <h2 class="mt-3 text-2xl font-bold text-white">
                                    {{ $selectedSimulation->title }}
                                </h2>

                                <p class="mt-3 text-sm leading-6 text-slate-400">
                                    {{ $result['executive_summary'] ?? 'Simulation completed.' }}
                                </p>
                            </div>

                            <div class="flex gap-3">
                                <div class="rounded-2xl bg-slate-950 px-5 py-4 text-center">
                                    <p class="text-xs uppercase tracking-wider text-slate-600">
                                        Purchase
                                    </p>

                                    <p class="mt-1 text-3xl font-bold text-cyan-400">
                                        {{ $result['purchase_probability'] ?? 0 }}%
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-950 px-5 py-4 text-center">
                                    <p class="text-xs uppercase tracking-wider text-slate-600">
                                        Score
                                    </p>

                                    <p class="mt-1 text-3xl font-bold text-violet-400">
                                        {{ $result['overall_score'] ?? 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8">
                            <h3 class="font-semibold text-white">
                                Dynamic Buyer Personas
                            </h3>

                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                @foreach ($personas as $persona)
                                    <article class="rounded-2xl border border-white/10 bg-slate-950 p-5">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="font-semibold text-white">
                                                    {{ $persona['name'] ?? 'Buyer Persona' }}
                                                </p>

                                                <p class="mt-1 text-xs text-slate-500">
                                                    {{ $persona['role'] ?? 'Stakeholder' }}
                                                </p>
                                            </div>

                                            <span class="rounded-full px-3 py-1 text-xs font-semibold
                                                {{ ($persona['position'] ?? '') === 'supportive'
                                                    ? 'bg-emerald-500/10 text-emerald-300'
                                                    : (($persona['position'] ?? '') === 'opposed'
                                                        ? 'bg-rose-500/10 text-rose-300'
                                                        : 'bg-amber-500/10 text-amber-300') }}"
                                            >
                                                {{ ucfirst($persona['position'] ?? 'neutral') }}
                                            </span>
                                        </div>

                                        <p class="mt-4 text-sm leading-6 text-slate-400">
                                            {{ $persona['argument'] ?? '' }}
                                        </p>

                                        @if (! empty($persona['concerns']))
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                @foreach ($persona['concerns'] as $concern)
                                                    <span class="rounded-full bg-slate-800 px-3 py-1 text-xs text-slate-400">
                                                        {{ $concern }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-8">
                            <h3 class="font-semibold text-white">
                                Committee Debate
                            </h3>

                            <div class="mt-4 space-y-3">
                                @foreach ($debate as $turn)
                                    <div class="rounded-2xl border border-white/5 bg-slate-950 p-5">
                                        <p class="text-sm font-semibold text-violet-300">
                                            {{ $turn['speaker'] ?? 'Committee Member' }}
                                        </p>

                                        <p class="mt-2 text-sm leading-6 text-slate-400">
                                            {{ $turn['message'] ?? '' }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-8 grid gap-5 md:grid-cols-2">
                            <div class="rounded-2xl border border-rose-500/20 bg-rose-500/5 p-5">
                                <h3 class="font-semibold text-rose-300">
                                    Key Objections
                                </h3>

                                <ul class="mt-4 space-y-3">
                                    @forelse ($objections as $objection)
                                        <li class="flex gap-3 text-sm leading-6 text-slate-400">
                                            <span class="text-rose-400">•</span>
                                            <span>{{ $objection }}</span>
                                        </li>
                                    @empty
                                        <li class="text-sm text-slate-500">
                                            No major objections returned.
                                        </li>
                                    @endforelse
                                </ul>
                            </div>

                            <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-5">
                                <h3 class="font-semibold text-emerald-300">
                                    Recommended Action
                                </h3>

                                <p class="mt-4 font-medium text-white">
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
                        </div>
                    @elseif ($selectedSimulation && $selectedSimulation->status === 'failed')
                        <div class="flex min-h-[650px] items-center justify-center text-center">
                            <div class="max-w-lg">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-500/10 text-2xl text-rose-400">
                                    !
                                </div>

                                <h2 class="mt-5 text-xl font-semibold text-white">
                                    Simulation failed
                                </h2>

                                <p class="mt-3 text-sm leading-6 text-rose-300">
                                    {{ $selectedSimulation->error_message }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="flex min-h-[650px] items-center justify-center text-center">
                            <div class="max-w-lg">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-violet-500/10 text-3xl text-violet-400">
                                    â—ˆ
                                </div>

                                <h2 class="mt-5 text-xl font-semibold text-white">
                                    Select graph8 data
                                </h2>

                                <p class="mt-3 text-sm leading-6 text-slate-500">
                                    Choose an actual synced company and run a simulation.
                                    Buyer personas, debate, objections and Relay8 actions
                                    will be generated dynamically.
                                </p>
                            </div>
                        </div>
                    @endif
                </section>

            </div>
        </div>
    </div>
</x-app-layout>
