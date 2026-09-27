import * as THREE from 'three';

export function createNexusScene(container, options = {}) {
    if (!(container instanceof HTMLElement)) {
        throw new Error('Robot viewport is missing.');
    }

    const reducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    const scene = new THREE.Scene();

    const camera = new THREE.PerspectiveCamera(
        31,
        1,
        0.1,
        80
    );

    camera.position.set(0, 1.5, 6.3);
    camera.lookAt(0, 1.38, 0);

    const renderer = new THREE.WebGLRenderer({
        alpha: true,
        antialias: true,
        powerPreference: 'low-power',
    });

    renderer.setPixelRatio(
        Math.min(window.devicePixelRatio || 1, 1.6)
    );

    renderer.setClearColor(0x000000, 0);
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.1;

    const canvas = renderer.domElement;

    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.display = 'block';

    canvas.setAttribute('role', 'img');

    canvas.setAttribute(
        'aria-label',
        options.label || 'Cream-suited RevenueNexus robot'
    );

    container.appendChild(canvas);

    const geometries = [];
    const materials = [];

    function surface(settings) {
        const result = new THREE.MeshPhysicalMaterial(settings);
        materials.push(result);
        return result;
    }

    const fabric = surface({
        color: 0xe5dfd2,
        roughness: 0.86,
        metalness: 0,
    });

    const shell = surface({
        color: 0xf3eee4,
        roughness: 0.32,
        metalness: 0.08,
        clearcoat: 0.5,
    });

    const seam = surface({
        color: 0xaaa291,
        roughness: 0.8,
        metalness: 0.05,
    });

    const dark = surface({
        color: 0x10100f,
        roughness: 0.55,
        metalness: 0.2,
    });

    const glass = surface({
        color: 0x030303,
        roughness: 0.15,
        metalness: 0.3,
        clearcoat: 1,
        clearcoatRoughness: 0.1,
    });

    const light = surface({
        color: 0xfff4dd,
        emissive: 0xffecd0,
        emissiveIntensity: 1.2,
        roughness: 0.3,
    });

    function group(parent, position = [0, 0, 0]) {
        const result = new THREE.Group();
        result.position.set(...position);
        parent.add(result);
        return result;
    }

    function mesh(geometry, material, parent, position) {
        geometries.push(geometry);

        const result = new THREE.Mesh(
            geometry,
            material
        );

        if (position) {
            result.position.set(...position);
        }

        parent.add(result);
        return result;
    }

    function sphere(parent, material, position, scale) {
        const result = mesh(
            new THREE.SphereGeometry(1, 28, 20),
            material,
            parent,
            position
        );

        result.scale.set(...scale);
        return result;
    }

    function capsule(
        parent,
        material,
        radius,
        length,
        position
    ) {
        return mesh(
            new THREE.CapsuleGeometry(
                radius,
                length,
                6,
                16
            ),
            material,
            parent,
            position
        );
    }

    function tapered(
        parent,
        material,
        top,
        bottom,
        height,
        position,
        depth = 1
    ) {
        const result = mesh(
            new THREE.CylinderGeometry(
                top,
                bottom,
                height,
                32,
                1,
                false
            ),
            material,
            parent,
            position
        );

        result.scale.z = depth;
        return result;
    }

    scene.add(
        new THREE.HemisphereLight(
            0xfff8ed,
            0x1b1915,
            1.6
        )
    );

    const key = new THREE.DirectionalLight(
        0xfff4df,
        3.2
    );

    key.position.set(-3, 4, 5);
    scene.add(key);

    const fill = new THREE.DirectionalLight(
        0xffffff,
        1.4
    );

    fill.position.set(3, 2, 3);
    scene.add(fill);

    const rim = new THREE.DirectionalLight(
        0xffeed4,
        2.8
    );

    rim.position.set(2, 3, -4);
    scene.add(rim);

    const root = group(scene);
    const body = group(root);

    // Tailored jacket with a continuous profile.
    const jacketProfile = [
        new THREE.Vector2(0, 1.13),
        new THREE.Vector2(0.29, 1.13),
        new THREE.Vector2(0.3, 1.18),
        new THREE.Vector2(0.265, 1.42),
        new THREE.Vector2(0.285, 1.68),
        new THREE.Vector2(0.34, 1.86),
        new THREE.Vector2(0.31, 1.93),
        new THREE.Vector2(0.14, 1.99),
        new THREE.Vector2(0, 1.99),
    ];

    const jacket = mesh(
        new THREE.LatheGeometry(jacketProfile, 48),
        fabric,
        body
    );

    jacket.scale.z = 0.62;

    // Cream collar and concealed neck.
    tapered(
        body,
        shell,
        0.105,
        0.13,
        0.13,
        [0, 2.01, 0],
        0.8
    );

    // Fine jacket opening seam.
    mesh(
        new THREE.BoxGeometry(0.006, 0.68, 0.006),
        seam,
        body,
        [0, 1.54, 0.182]
    );

    for (let index = 0; index < 3; index++) {
        sphere(
            body,
            seam,
            [0.025, 1.61 - index * 0.14, 0.19],
            [0.01, 0.01, 0.007]
        );
    }

    // Small pocket seam.
    mesh(
        new THREE.BoxGeometry(0.095, 0.006, 0.006),
        seam,
        body,
        [-0.16, 1.76, 0.181]
    );

    const head = group(body, [0, 2.27, 0]);

    sphere(
        head,
        shell,
        [0, 0, 0],
        [0.24, 0.29, 0.225]
    );

    // Curved black visor within the cream shell.
    sphere(
        head,
        seam,
        [0, -0.008, 0.171],
        [0.199, 0.234, 0.084]
    );

    sphere(
        head,
        glass,
        [0, -0.008, 0.18],
        [0.185, 0.22, 0.081]
    );

    const eyes = [];

    for (const side of [-1, 1]) {
        const eye = sphere(
            head,
            light,
            [side * 0.07, 0.027, 0.255],
            [0.022, 0.041, 0.009]
        );

        eyes.push(eye);

        const ear = mesh(
            new THREE.CylinderGeometry(
                0.087,
                0.087,
                0.051,
                32
            ),
            shell,
            head,
            [side * 0.244, 0.018, -0.012]
        );

        ear.rotation.z = Math.PI / 2;

        const earRing = mesh(
            new THREE.TorusGeometry(
                0.067,
                0.006,
                8,
                32
            ),
            seam,
            head,
            [side * 0.272, 0.018, -0.012]
        );

        earRing.rotation.y = Math.PI / 2;
    }

    const headband = mesh(
        new THREE.TorusGeometry(
            0.256,
            0.013,
            8,
            48,
            Math.PI
        ),
        shell,
        head,
        [0, 0.025, -0.025]
    );

    headband.scale.y = 1.09;

    const mouth = sphere(
        head,
        light,
        [0, -0.091, 0.255],
        [0.025, 0.006, 0.007]
    );

    const arms = [];

    for (const side of [-1, 1]) {
        const shoulder = group(
            body,
            [side * 0.315, 1.87, 0]
        );

        sphere(
            shoulder,
            fabric,
            [side * 0.012, -0.047, 0],
            [0.11, 0.145, 0.123]
        );

        tapered(
            shoulder,
            fabric,
            0.095,
            0.074,
            0.33,
            [0, -0.23, 0],
            0.95
        );

        const elbow = group(
            shoulder,
            [0, -0.4, 0]
        );

        sphere(
            elbow,
            fabric,
            [0, 0, 0],
            [0.075, 0.077, 0.075]
        );

        tapered(
            elbow,
            fabric,
            0.075,
            0.062,
            0.3,
            [0, -0.15, 0],
            0.94
        );

        tapered(
            elbow,
            shell,
            0.063,
            0.062,
            0.036,
            [0, -0.313, 0]
        );

        const hand = group(
            elbow,
            [0, -0.365, 0]
        );

        sphere(
            hand,
            shell,
            [0, -0.025, 0],
            [0.058, 0.07, 0.032]
        );

        for (let index = 0; index < 4; index++) {
            const fingerLength =
                index === 0 || index === 3 ? 0.039 : 0.051;

            const finger = capsule(
                hand,
                shell,
                0.009,
                fingerLength,
                [(index - 1.5) * 0.021, -0.094, 0.004]
            );

            finger.rotation.x = -0.12;
        }

        const thumb = capsule(
            hand,
            shell,
            0.012,
            0.036,
            [-side * 0.05, -0.035, 0.018]
        );

        thumb.rotation.z = side * 0.5;
        shoulder.rotation.z = side * 0.065;

        arms.push({
            side,
            shoulder,
            elbow,
            hand,
        });
    }

    // Straight tailored trousers.
    sphere(
        body,
        fabric,
        [0, 1.14, -0.01],
        [0.25, 0.14, 0.15]
    );

    const legs = [];

    for (const side of [-1, 1]) {
        const hip = group(
            body,
            [side * 0.13, 1.15, 0]
        );

        tapered(
            hip,
            fabric,
            0.12,
            0.092,
            0.48,
            [0, -0.24, 0],
            1.06
        );

        const knee = group(
            hip,
            [0, -0.48, 0]
        );

        sphere(
            knee,
            fabric,
            [0, 0, 0],
            [0.093, 0.067, 0.1]
        );

        tapered(
            knee,
            fabric,
            0.092,
            0.085,
            0.43,
            [0, -0.22, 0],
            1.06
        );

        // Trouser crease.
        mesh(
            new THREE.BoxGeometry(
                0.003,
                0.36,
                0.003
            ),
            seam,
            knee,
            [0, -0.21, 0.097]
        );

        sphere(
            knee,
            shell,
            [0, -0.486, 0.073],
            [0.095, 0.058, 0.18]
        );

        legs.push({ side, hip, knee });
    }

    // Restrained floor marker.
    const floorRing = mesh(
        new THREE.TorusGeometry(
            0.59,
            0.002,
            6,
            80
        ),
        seam,
        scene,
        [0, 0.055, 0]
    );

    floorRing.rotation.x = Math.PI / 2;

    let disposed = false;
    let speaking = false;
    let walking = false;
    let entrance = null;
    let visible = true;
    let elapsed = 0;
    let previousTime = null;
    let frame = 0;

    const pointer = new THREE.Vector2();

    function pointerMove(event) {
        const bounds = container.getBoundingClientRect();

        if (!bounds.width || !bounds.height) {
            return;
        }

        pointer.x =
            ((event.clientX - bounds.left) / bounds.width) * 2 - 1;

        pointer.y =
            ((event.clientY - bounds.top) / bounds.height) * 2 - 1;
    }

    function pointerLeave() {
        pointer.set(0, 0);
    }

    function resize() {
        const width = Math.max(container.clientWidth, 1);
        const height = Math.max(container.clientHeight, 1);

        renderer.setSize(width, height, false);

        camera.aspect = width / height;
        camera.position.z = camera.aspect < 0.6 ? 7.4 : 6.3;
        camera.updateProjectionMatrix();
    }

    container.addEventListener('pointermove', pointerMove);
    container.addEventListener('pointerleave', pointerLeave);

    const resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(container);

    const intersectionObserver = new IntersectionObserver(
        ([entry]) => {
            visible = entry.isIntersecting;
        }
    );

    intersectionObserver.observe(container);
    resize();

    function animate(timestamp) {
        if (disposed) {
            return;
        }

        frame = requestAnimationFrame(animate);

        const delta = previousTime === null
            ? 0
            : Math.min((timestamp - previousTime) / 1000, 0.05);

        previousTime = timestamp;

        if (!visible || document.hidden) {
            return;
        }

        elapsed += delta;

        let walkAmount = walking && !reducedMotion ? 1 : 0;

        if (entrance) {
            entrance.elapsed += delta;

            const progress = Math.min(
                entrance.elapsed / entrance.duration,
                1
            );

            const smooth =
                progress * progress * (3 - 2 * progress);

            root.position.z = -10 * (1 - smooth);

            walkAmount = reducedMotion
                ? 0
                : Math.min(1, (1 - progress) * 6);

            if (progress >= 1) {
                entrance = null;
                root.position.z = 0;
            }
        }

        const time = reducedMotion ? 0 : elapsed;
        const stride = Math.sin(time * 6.5);

        body.position.y = walkAmount
            ? Math.abs(stride) * 0.022 * walkAmount
            : Math.sin(time * 1.6) * 0.006;

        root.rotation.y = THREE.MathUtils.damp(
            root.rotation.y,
            reducedMotion ? 0 : -0.08 + pointer.x * 0.13,
            5,
            delta
        );

        head.rotation.y = THREE.MathUtils.damp(
            head.rotation.y,
            reducedMotion ? 0 : pointer.x * 0.1,
            5,
            delta
        );

        head.rotation.x = reducedMotion
            ? 0
            : pointer.y * 0.04 +
                (speaking ? Math.sin(time * 3) * 0.03 : 0);

        const blinking =
            !reducedMotion && elapsed % 5.8 > 5.65;

        eyes.forEach((eye) => {
            eye.scale.y = blinking ? 0.006 : 0.041;
        });

        mouth.scale.y = speaking && !reducedMotion
            ? 0.006 + Math.abs(Math.sin(time * 16)) * 0.013
            : 0.006;

        arms.forEach((arm) => {
            const gesture = speaking && !reducedMotion
                ? 0.4 + Math.sin(time * 2.2 + arm.side) * 0.15
                : 0;

            arm.shoulder.rotation.x = THREE.MathUtils.damp(
                arm.shoulder.rotation.x,
                stride * 0.28 * walkAmount * arm.side -
                    gesture * 0.35,
                7,
                delta
            );

            arm.shoulder.rotation.z = THREE.MathUtils.damp(
                arm.shoulder.rotation.z,
                arm.side * (0.065 + gesture * 0.25),
                6,
                delta
            );

            arm.elbow.rotation.x = THREE.MathUtils.damp(
                arm.elbow.rotation.x,
                -0.04 - gesture * 1.25,
                6,
                delta
            );

            arm.hand.rotation.z =
                gesture * arm.side * 0.2;
        });

        legs.forEach((leg) => {
            leg.hip.rotation.x =
                stride * 0.3 * walkAmount * leg.side;

            leg.knee.rotation.x =
                Math.max(0, -stride * leg.side) *
                0.36 * walkAmount;
        });

        renderer.render(scene, camera);
    }

    frame = requestAnimationFrame(animate);

    return {
        setSpeaking(value) {
            speaking = Boolean(value);
        },

        setWalking(value) {
            walking = Boolean(value);
        },

        playEntrance(seconds = 4.5) {
            if (reducedMotion) {
                return;
            }

            root.position.z = -10;

            entrance = {
                elapsed: 0,
                duration: Math.max(0.5, Number(seconds) || 4.5),
            };
        },

        destroy() {
            if (disposed) {
                return;
            }

            disposed = true;

            cancelAnimationFrame(frame);
            resizeObserver.disconnect();
            intersectionObserver.disconnect();

            container.removeEventListener(
                'pointermove',
                pointerMove
            );

            container.removeEventListener(
                'pointerleave',
                pointerLeave
            );

            geometries.forEach((geometry) => geometry.dispose());
            materials.forEach((material) => material.dispose());

            renderer.dispose();
            canvas.remove();
        },
    };
}

