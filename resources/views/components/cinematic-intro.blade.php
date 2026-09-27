<div x-data="revenueIntro">
    <template x-if="visible">
        <section
            class="rt-intro"
            x-bind:class="{
                'rt-intro--leaving': fading
            }"
            aria-label="RevenueTwin8 introduction"
        >
            <div class="rt-intro-light"></div>

            <div class="rt-intro-robot">
                <x-twin-robot
                    name="Revenue Twin"
                    size="hero"
                    :show-identity="false"
                    :entrance="true"
                />
            </div>

            <div
                class="rt-intro-brand"
                x-bind:class="{
                    'is-visible': brand
                }"
            >
                <span class="rt-eyebrow">
                    See the next move.
                </span>

                <h1>
                    RevenueTwin<span>8</span>
                </h1>
            </div>

            <button
                type="button"
                class="rt-intro-skip"
                x-on:click="finish()"
            >
                Skip intro ↗
            </button>

            <div class="rt-intro-progress"></div>
        </section>
    </template>
</div>