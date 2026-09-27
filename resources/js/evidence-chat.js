document.addEventListener('alpine:init', () => {
    window.Alpine.data('evidenceChat', () => ({
        open: false,
        loading: false,
        message: '',
        messages: [
            {
                role: 'assistant',
                content: 'Ask me anything about RevenueNexus8 — its modules, AI agents, architecture, graph8 integration, or decision workflow.',
                sources: [],
            },
        ],

        toggle() {
            this.open = !this.open;

            if (this.open) {
                this.$nextTick(() => {
                    this.$refs.input?.focus();
                    this.scrollToBottom();
                });
            }
        },

        close() {
            this.open = false;
        },

        async send() {
            const question = this.message.trim();

            if (!question || this.loading) {
                return;
            }

            const history = this.messages
                .slice(-8)
                .map((item) => ({
                    role: item.role,
                    content: item.content,
                }));

            this.messages.push({
                role: 'user',
                content: question,
                sources: [],
            });

            this.message = '';
            this.loading = true;
            this.scrollToBottom();

            try {
                const response = await fetch('/evidence8/chat', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') ?? '',
                    },
                    body: JSON.stringify({
                        message: question,
                        history,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(
                        data.message
                        ?? 'Evidence8 could not answer.'
                    );
                }

                this.messages.push({
                    role: 'assistant',
                    content: data.answer,
                    sources: data.sources ?? [],
                });
            } catch (error) {
                this.messages.push({
                    role: 'assistant',
                    content: error.message
                        ?? 'Evidence8 is temporarily unavailable.',
                    sources: [],
                });
            } finally {
                this.loading = false;
                this.scrollToBottom();
            }
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const thread = this.$refs.thread;

                if (thread) {
                    thread.scrollTop = thread.scrollHeight;
                }
            });
        },
    }));
});