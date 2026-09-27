@props(['simulation'])

@php
    $result = $simulation->result_data ?? [];
    $input = $simulation->input_data ?? [];

    $turns = [];
    $speakers = [];
    $prefix = 'simulation-'.$simulation->id.'-speaker-';

    $addTurn = function ($name, $text, $role = 'Simulated stakeholder')
        use (&$turns, &$speakers, $prefix) {
        if (! is_string($text) || trim($text) === '') {
            return;
        }

        $name = is_string($name) && trim($name) !== ''
            ? trim($name)
            : 'AI Agent';

        if (! isset($speakers[$name])) {
            $speakers[$name] = [
                'id' => $prefix.count($speakers),
                'name' => $name,
                'role' => $role,
            ];
        }

        $turns[] = [
            'speaker' => $name,
            'speaker_id' => $speakers[$name]['id'],
            'text' => trim($text),
        ];
    };

    if ($simulation->module === 'boardroom8') {
        $roles = [];

        foreach (($result['personas'] ?? []) as $persona) {
            if (is_array($persona) && isset($persona['name'])) {
                $roles[$persona['name']] =
                    $persona['role'] ?? 'Simulated stakeholder';
            }
        }

        foreach (($result['debate'] ?? []) as $turn) {
            if (! is_array($turn)) {
                continue;
            }

            $name = $turn['speaker'] ?? 'Committee Member';

            $addTurn(
                $name,
                $turn['message'] ?? '',
                $roles[$name] ?? 'Simulated stakeholder'
            );
        }
    }

    if ($simulation->module === 'negotiator8') {
        $buyer = data_get($input, 'buyer');

        if (! is_string($buyer) || trim($buyer) === '') {
            $buyer = 'Buyer';
        }

        foreach (($result['rounds'] ?? []) as $round) {
            if (! is_array($round)) {
                continue;
            }

            $addTurn(
                $round['buyer_name'] ?? $buyer.' â€” Buyer',
                $round['buyer_move'] ?? '',
                'Simulated buyer'
            );

            $addTurn(
                $round['seller_name'] ?? 'Seller Agent',
                $round['seller_response'] ?? '',
                'Simulated seller'
            );
        }
    }

    $configuration = [
        'turns' => $turns,
        'speakers' => array_values($speakers),
        'autoRun' => $simulation->status === 'completed'
            && str_contains(
                strtolower((string) session('success', '')),
                'completed'
            ),
    ];
@endphp

@if(count($turns))
    <section
        class="rt-conversation"
        x-data="twinConversation({{
            Illuminate\Support\Js::from($configuration)
        }})"
    >
        <div class="rt-conversation-heading">
            <div>
                <p class="rt-eyebrow">
                    AI Strategy Conversation
                </p>

                <h3>
                    {{ $simulation->module === 'boardroom8'
                        ? 'Hear the room.'
                        : 'Hear the negotiation.' }}
                </h3>
            </div>

            <span
                class="rt-pill"
                x-text="(index + 1) + ' / ' + turns.length"
            ></span>
        </div>

        <div class="rt-conversation-robots">
            @foreach($speakers as $speaker)
                <article
                    class="rt-conversation-agent"
                    x-bind:class="{
                        'is-speaking':
                            playing &&
                            !paused &&
                            current.speaker_id === @js($speaker['id'])
                    }"
                >
                    <x-nexus-robot
                        :name="$speaker['name']"
                        :role="$speaker['role']"
                        :speaker-id="$speaker['id']"
                        size="small"
                    />
                </article>
            @endforeach
        </div>

        <div
            class="rt-conversation-caption"
            aria-live="polite"
            aria-atomic="true"
        >
            <span
                class="rt-eyebrow"
                x-text="current.speaker"
            ></span>

            <p x-text="current.text"></p>
        </div>

        <div
            class="rt-new-run-controls"
            x-cloak
            x-show="autoRun"
            style="display:flex;align-items:center;gap:10px;flex-wrap:wrap"
        >
            <button
                type="button"
                class="rt-button"
                x-on:click="toggleMute()"
                x-bind:disabled="!supported || finished"
                x-bind:aria-pressed="muted"
                x-text="muted ? 'Unmute' : 'Mute'"
            ></button>

            <button
                type="button"
                class="rt-button rt-button-light"
                x-show="needsStart"
                x-on:click="play()"
            >
                Start audio
            </button>

            <span
                class="rt-muted"
                x-text="finished
                    ? 'Conversation complete'
                    : (muted ? 'Sound off' : 'Sound on')"
            ></span>
        </div>

        <div
            class="rt-conversation-controls"
            x-cloak
            x-show="!autoRun"
        >
            <button
                type="button"
                class="rt-button rt-button-light"
                x-on:click="play()"
                x-bind:disabled="
                    !supported || (playing && !paused)
                "
                x-text="
                    paused
                        ? 'Resume'
                        : (finished ? 'Play again' : 'Play conversation')
                "
            ></button>

            <button
                type="button"
                class="rt-button"
                x-on:click="pause()"
                x-bind:disabled="!playing || paused"
            >
                Pause
            </button>

            <button
                type="button"
                class="rt-button"
                x-on:click="stop()"
                x-bind:disabled="!playing"
            >
                Stop
            </button>

            <button
                type="button"
                class="rt-button"
                x-on:click="selectTurn(index + 1)"
                x-bind:disabled="index >= turns.length - 1"
            >
                Next turn â†’
            </button>

            <label class="rt-conversation-speed">
                Speed

                <select
                    x-model="rate"
                    x-bind:disabled="playing"
                    aria-label="Playback speed"
                >
                    <option value="0.85">0.85Ã—</option>
                    <option value="1">1Ã—</option>
                    <option value="1.15">1.15Ã—</option>
                </select>
            </label>
        </div>

        <p
            x-cloak
            x-show="!supported"
            class="rt-conversation-note"
        >
            Voice playback is unavailable in this browser.
            The complete transcript remains below.
        </p>

        <p
            x-cloak
            x-show="error"
            x-text="error"
            class="rt-conversation-note"
            role="status"
        ></p>

        <p
            x-cloak
            x-show="finished"
            class="rt-conversation-note"
            role="status"
        >
            Conversation complete. Review the decision report below.
        </p>

        <details class="rt-conversation-transcript">
            <summary>
                Conversation transcript
            </summary>

            <div class="rt-conversation-turns">
                @foreach($turns as $turn)
                    <button
                        type="button"
                        class="rt-conversation-turn"
                        x-on:click="selectTurn({{ $loop->index }})"
                        x-bind:class="{
                            'is-current': index === {{ $loop->index }}
                        }"
                    >
                        <strong>
                            {{ $turn['speaker'] }}
                        </strong>

                        <span>
                            {{ $turn['text'] }}
                        </span>
                    </button>
                @endforeach
            </div>
        </details>

        <p class="rt-conversation-note">
            Generated simulation dialogue Â· Device voice playback
        </p>
    </section>

    @once
        <style>
            .rt-conversation {
                margin-bottom: 30px;
                padding: 22px;
                border: 1px solid rgba(238, 233, 223, 0.15);
                border-radius: 18px;
                background: linear-gradient(#111110, #050505);
                color: #eee9df;
            }

            .rt-conversation-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 15px;
                margin-bottom: 22px;
            }

            .rt-conversation-heading h3 {
                margin-top: 8px;
                font-size: 24px;
                font-weight: 500;
                letter-spacing: -0.8px;
            }

            .rt-conversation-robots {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
            }

            .rt-conversation-agent {
                min-width: 0;
                overflow: hidden;
                padding: 8px 4px 12px;
                border: 1px solid rgba(238, 233, 223, 0.08);
                border-radius: 14px;
                background: radial-gradient(
                    ellipse at 50% 40%,
                    rgba(238, 233, 223, 0.04),
                    transparent 70%
                );
                transition:
                    border-color 250ms ease,
                    background-color 250ms ease;
            }

            .rt-conversation-agent.is-speaking {
                border-color: rgba(238, 233, 223, 0.65);
                background-color: rgba(238, 233, 223, 0.035);
            }

            .rt-conversation-agent .rt3d-nexus--small {
                height: 265px;
            }

            .rt-conversation-agent .rt3d-nexus__identity {
                width: calc(100% - 12px);
                max-width: none;
                padding: 9px;
                gap: 7px;
            }

            .rt-conversation-agent .rt3d-nexus__identity > div {
                min-width: 0;
                flex: 1;
            }

            .rt-conversation-agent .rt3d-nexus__identity strong {
                overflow-wrap: anywhere;
                font-size: 10px;
            }

            .rt-conversation-agent .rt3d-nexus__identity small {
                font-size: 8px;
            }

            .rt-conversation-caption {
                min-height: 135px;
                padding: 24px 0;
            }

            .rt-conversation-caption > p {
                margin-top: 12px;
                font-size: 15px;
                line-height: 1.85;
                color: #d9d1c2;
            }

            .rt-conversation-controls {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 8px;
            }

            .rt-conversation-controls .rt-button {
                padding: 10px 13px;
                font-size: 11px;
            }

            .rt-conversation-speed {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .rt-conversation-speed select {
                width: 86px;
                padding: 8px !important;
                font-size: 11px;
            }

            .rt-conversation-note {
                margin-top: 15px;
                color: #9c9280;
                font-size: 10px;
                line-height: 1.7;
            }

            .rt-conversation-transcript {
                margin-top: 22px;
                border-top: 1px solid rgba(238, 233, 223, 0.12);
                padding-top: 17px;
            }

            .rt-conversation-transcript summary {
                cursor: pointer;
                font-size: 12px;
            }

            .rt-conversation-turns {
                display: grid;
                gap: 8px;
                margin-top: 15px;
            }

            .rt-conversation-turn {
                display: block;
                width: 100%;
                padding: 14px;
                border: 1px solid transparent;
                border-radius: 10px;
                background: #11110f;
                text-align: left;
            }

            .rt-conversation-turn.is-current {
                border-color: #bcb09a;
                background: #1b1914;
            }

            .rt-conversation-turn strong {
                display: block;
                font-size: 11px;
                color: #eee9df;
            }

            .rt-conversation-turn span {
                display: block;
                margin-top: 7px;
                color: #aaa08d;
                font-size: 12px;
                line-height: 1.8;
            }

            @media (max-width: 600px) {
                .rt-conversation {
                    padding: 14px;
                }

                .rt-conversation-agent .rt3d-nexus--small {
                    height: 220px;
                }

                .rt-conversation-heading h3 {
                    font-size: 20px;
                }

                .rt-conversation-agent .rt3d-nexus__identity > span {
                    display: none;
                }
            }
        </style>
    @endonce
@endif
