<div
    class="evidence8"
    x-data="evidenceChat"
    x-cloak
>
    <div
        class="evidence8-panel"
        x-show="open"
        x-transition:enter="evidence8-enter"
        x-transition:enter-start="evidence8-enter-start"
        x-transition:enter-end="evidence8-enter-end"
        x-transition:leave="evidence8-leave"
        x-transition:leave-start="evidence8-leave-start"
        x-transition:leave-end="evidence8-leave-end"
        @keydown.escape.window="close"
    >
        <header class="evidence8-header">
            <div>
                <span class="evidence8-eyebrow">
                    Qdrant RAG
                </span>

                <h2>Evidence8</h2>

                <p>RevenueNexus8 knowledge</p>
            </div>

            <button
                type="button"
                class="evidence8-close"
                @click="close"
                aria-label="Close Evidence8"
            >
                ×
            </button>
        </header>

        <div
            class="evidence8-thread"
            x-ref="thread"
        >
            <template
                x-for="(item, index) in messages"
                :key="index"
            >
                <article
                    class="evidence8-message"
                    :class="{
                        'evidence8-message--user':
                            item.role === 'user'
                    }"
                >
                    <span
                        class="evidence8-speaker"
                        x-text="
                            item.role === 'user'
                                ? 'You'
                                : 'Evidence8'
                        "
                    ></span>

                    <p x-text="item.content"></p>

                    <template
                        x-if="
                            item.sources
                            && item.sources.length
                        "
                    >
                        <div class="evidence8-sources">
                            <span>Sources</span>

                            <template
                                x-for="source in item.sources"
                                :key="source"
                            >
                                <small
                                    x-text="source"
                                ></small>
                            </template>
                        </div>
                    </template>
                </article>
            </template>

            <div
                class="evidence8-thinking"
                x-show="loading"
            >
                <i></i>
                <i></i>
                <i></i>
            </div>
        </div>

        <form
            class="evidence8-form"
            @submit.prevent="send"
        >
            <textarea
                x-ref="input"
                x-model="message"
                rows="1"
                maxlength="2000"
                placeholder="Ask anything about RevenueNexus8..."
                @keydown.enter="
                    if (!$event.shiftKey) {
                        $event.preventDefault();
                        send();
                    }
                "
            ></textarea>

            <button
                type="submit"
                :disabled="
                    loading
                    || !message.trim()
                "
            >
                Send
            </button>
        </form>
    </div>

    <div
        class="evidence8-availability"
        x-show="!open"
        x-transition
    >
        <strong>Evidence8</strong>
        <span>24/7</span>
    </div>

    <button
        type="button"
        class="evidence8-trigger"
        :class="{
            'evidence8-trigger--active': open
        }"
        @click="toggle"
        aria-label="Open Evidence8 assistant"
    >
        <span class="evidence8-pulse"></span>

        <span class="evidence8-avatar">
            <img
                src="{{ asset('images/chatbot.png') }}"
                alt="Evidence8 AI assistant"
            >
        </span>
    </button>
</div>