import Alpine from 'alpinejs';

Alpine.data('twinConversation', (configuration) => {
    const turns = configuration.turns || [];
    const speakers = configuration.speakers || [];

    let token = 0;
    let utterance = null;
    let chunks = [];
    let chunkIndex = 0;
    let pageHideHandler = null;

    function splitSpeech(text) {
        const result = [];
        let current = '';

        for (const word of String(text).trim().split(/\s+/)) {
            if (current && current.length + word.length > 200) {
                result.push(current);
                current = word;
            } else {
                current += (current ? ' ' : '') + word;
            }
        }

        if (current) {
            result.push(current);
        }

        return result;
    }

    return {
        turns,
        index: 0,
        playing: false,
        paused: false,
        finished: false,
        error: '',
        rate: 1,
        muted: false,
        autoRun: Boolean(configuration.autoRun),
        needsStart: false,

        supported:
            'speechSynthesis' in window &&
            'SpeechSynthesisUtterance' in window,

        get current() {
            return this.turns[this.index] || {
                speaker: '',
                text: '',
                speaker_id: '',
            };
        },

        init() {
            pageHideHandler = () => this.stop();

            window.addEventListener(
                'pagehide',
                pageHideHandler
            );

            if (!this.supported) {
                return;
            }

            window.speechSynthesis.getVoices();

            if (this.autoRun) {
                this.$nextTick(() => this.play());
            }
        },

        signal(speaking) {
            window.dispatchEvent(
                new CustomEvent('rt-speaker', {
                    detail: {
                        speakerId: this.current.speaker_id,
                        speaking,
                    },
                })
            );
        },

        play() {
            if (!this.supported || !this.turns.length) {
                return;
            }

            if (this.paused) {
                window.speechSynthesis.resume();
                this.paused = false;
                this.signal(true);
                return;
            }

            if (this.playing) {
                return;
            }

            if (this.finished) {
                this.index = 0;
            }

            this.error = '';
            this.needsStart = false;
            this.finished = false;
            this.playing = true;

            window.speechSynthesis.cancel();

            const run = ++token;
            this.prepareTurn(run);
        },

        prepareTurn(run) {
            if (run !== token || !this.playing) {
                return;
            }

            chunks = splitSpeech(this.current.text);
            chunkIndex = 0;

            this.speakChunk(run);
        },

        speakChunk(run) {
            if (run !== token || !this.playing) {
                return;
            }

            if (chunkIndex >= chunks.length) {
                this.finishTurn(run);
                return;
            }

            utterance = new SpeechSynthesisUtterance(
                chunks[chunkIndex]
            );

            const voices = window.speechSynthesis
                .getVoices()
                .filter((voice) => voice.lang.startsWith('en'));

            const speakerIndex = Math.max(
                0,
                speakers.findIndex(
                    (speaker) =>
                        speaker.id === this.current.speaker_id
                )
            );

            if (voices.length) {
                utterance.voice =
                    voices[speakerIndex % voices.length];

                utterance.lang = utterance.voice.lang;
            } else {
                utterance.lang = 'en-US';
            }

            utterance.rate = Number(this.rate);
            utterance.pitch = speakerIndex % 2 === 0 ? 0.95 : 1.05;
            utterance.volume = this.muted ? 0 : 1;

            utterance.onstart = () => {
                if (run === token && !this.paused) {
                    this.needsStart = false;
                    this.signal(true);
                }
            };

            utterance.onend = () => {
                if (run !== token) {
                    return;
                }

                chunkIndex++;
                this.speakChunk(run);
            };

            utterance.onerror = (event) => {
                if (run !== token) {
                    return;
                }

                this.signal(false);
                this.playing = false;
                this.paused = false;

                if (
                    event.error === 'canceled' ||
                    event.error === 'interrupted'
                ) {
                    return;
                }

                this.needsStart = true;

                this.error = event.error === 'not-allowed'
                    ? 'Your browser requires one click to start audio.'
                    : 'Audio could not start. Press Start audio to retry.';
            };

            window.speechSynthesis.speak(utterance);
        },

        toggleMute() {
            this.muted = !this.muted;

            if (!this.playing || this.paused) {
                return;
            }

            // Restart only the current short speech segment.
            // This applies volume immediately across browsers.
            const run = ++token;

            window.speechSynthesis.cancel();
            this.speakChunk(run);
        },

        finishTurn(run) {
            if (run !== token) {
                return;
            }

            this.signal(false);

            if (this.index + 1 < this.turns.length) {
                this.index++;
                this.prepareTurn(run);
                return;
            }

            this.playing = false;
            this.paused = false;
            this.finished = true;
        },

        pause() {
            if (!this.playing || this.paused) {
                return;
            }

            window.speechSynthesis.pause();

            this.paused = true;
            this.signal(false);
        },

        stop() {
            token++;
            this.signal(false);

            if (this.supported) {
                window.speechSynthesis.cancel();
            }

            this.playing = false;
            this.paused = false;
            utterance = null;
        },

        selectTurn(position) {
            this.stop();

            this.index = Math.max(
                0,
                Math.min(position, this.turns.length - 1)
            );

            this.finished = false;
            this.error = '';
        },

        restart() {
            this.selectTurn(0);
            this.play();
        },

        destroy() {
            this.stop();

            window.removeEventListener(
                'pagehide',
                pageHideHandler
            );
        },
    };
});