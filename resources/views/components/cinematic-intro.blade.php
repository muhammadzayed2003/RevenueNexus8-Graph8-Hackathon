<div x-data="revenueIntro">
    <template x-if="visible">
        <section
            class="rt-intro"
            x-bind:class="{
                'rt-intro--leaving': fading
            }"
            aria-label="RevenueNexus8 introduction"
        >
            <div class="rt-intro-light"></div>

            <div class="rt-intro-robot">
                <x-nexus-robot
                    name="Revenue Nexus"
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
                    RevenueNexus<span>8</span>
                </h1>
            </div>

            <button
                type="button"
                class="rt-intro-skip"
                x-on:click="finish()"
            >
                Skip intro â†—
            </button>

            <div class="rt-intro-progress"></div>
        </section>
    </template>
</div>

