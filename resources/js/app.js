import './evidence-chat';
import './agent-voice';
import Alpine from 'alpinejs';
import { createNexusScene } from './nexus-scene';

window.Alpine = Alpine;

function decideIntro() {
    const navigation = performance
        .getEntriesByType('navigation')[0];

    const navigationType = navigation?.type;

    let visited = false;

    try {
        visited = sessionStorage.getItem(
            'rt8:site-visited'
        ) === 'yes';

        // Mark immediately, before the user clicks anything.
        sessionStorage.setItem(
            'rt8:site-visited',
            'yes'
        );

        // Remove the previous navigation marker.
        sessionStorage.removeItem(
            'rt8:internal-navigation'
        );
    } catch {
        // Fallback for browsers that disable session storage.
        try {
            visited =
                new URL(document.referrer).origin ===
                location.origin;
        } catch {
            visited = false;
        }
    }

    if (navigationType === 'reload') {
        return true;
    }

    if (navigationType === 'back_forward') {
        return false;
    }

    return !visited;
}

const showIntro = decideIntro();

Alpine.data('revenueRobot', (settings = {}) => {
    let scene = null;

    return {
        failed: false,
        active: false,

        init() {
            this.$nextTick(() => {
                try {
                    scene = createNexusScene(
                        this.$refs.viewport,
                        {
                            label: settings.name,
                        }
                    );
                } catch (error) {
                    this.failed = true;
                    console.error(
                        'Robot display failed:',
                        error
                    );
                }
            });
        },

        changeSpeaker(detail) {
            this.active = Boolean(
                detail?.speaking &&
                settings.speakerId != null &&
                String(detail.speakerId) ===
                    String(settings.speakerId)
            );

            scene?.setSpeaking(this.active);
        },

        destroy() {
            scene?.destroy();
            scene = null;
        },
    };
});

Alpine.data('revenueIntro', () => ({
    visible: false,
    brand: false,
    fading: false,
    timers: [],
    previousOverflow: '',
    scrollLocked: false,
    pageHideHandler: null,

    init() {
        this.pageHideHandler = () => this.finish();

        window.addEventListener(
            'pagehide',
            this.pageHideHandler
        );

        const reducedMotion = window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        ).matches;

        if (!showIntro || reducedMotion) {
            return;
        }

        this.previousOverflow =
            document.documentElement.style.overflow;

        document.documentElement.style.overflow = 'hidden';

        this.scrollLocked = true;
        this.visible = true;

        this.timers = [
            window.setTimeout(() => {
                this.brand = true;
            }, 1500),

            window.setTimeout(() => {
                this.fading = true;
            }, 4500),

            window.setTimeout(() => {
                this.finish();
            }, 5200),
        ];
    },

    finish() {
        this.timers.forEach((timer) => {
            window.clearTimeout(timer);
        });

        this.timers = [];
        this.visible = false;

        if (this.scrollLocked) {
            document.documentElement.style.overflow =
                this.previousOverflow;

            this.scrollLocked = false;
        }
    },

    destroy() {
        this.finish();

        window.removeEventListener(
            'pagehide',
            this.pageHideHandler
        );
    },
}));

Alpine.start();
