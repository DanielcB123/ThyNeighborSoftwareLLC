<script setup lang="ts">
import {
    AmbientLight,
    BufferAttribute,
    BufferGeometry,
    Clock,
    Color,
    Group,
    IcosahedronGeometry,
    Mesh,
    MeshStandardMaterial,
    PerspectiveCamera,
    PointLight,
    Points,
    PointsMaterial,
    Scene,
    TorusKnotGeometry,
    WebGLRenderer,
} from "three";
import { onBeforeUnmount, onMounted, ref } from "vue";

const containerElement = ref<HTMLElement | null>(null);
const canvasElement = ref<HTMLCanvasElement | null>(null);
const reducedMotionEnabled = ref(false);

let releaseScene: (() => void) | null = null;
let mediaQueryList: MediaQueryList | null = null;
let mediaQueryListener:
    | ((this: MediaQueryList, event: MediaQueryListEvent) => void)
    | null = null;

function lerp(start: number, end: number, factor: number): number {
    return start + (end - start) * factor;
}

function mountSignalFieldScene(): (() => void) | null {
    const container = containerElement.value;
    const canvas = canvasElement.value;

    if (!container || !canvas) {
        return null;
    }

    const scene = new Scene();
    scene.background = null;

    const camera = new PerspectiveCamera(34, 1, 0.1, 50);
    camera.position.set(0, 0, 8.5);

    const renderer = new WebGLRenderer({
        canvas,
        alpha: true,
        antialias: true,
        powerPreference: "high-performance",
    });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.75));

    const ambientLight = new AmbientLight("#95a7ff", 0.58);
    const keyLight = new PointLight("#8a7eff", 4.2, 24, 1.5);
    keyLight.position.set(3.5, 2.8, 5.5);
    const fillLight = new PointLight("#2df6d2", 3, 26, 1.75);
    fillLight.position.set(-4.2, -3.1, 3.8);
    scene.add(ambientLight, keyLight, fillLight);

    const geometryCluster = new Group();
    scene.add(geometryCluster);

    const knotGeometry = new TorusKnotGeometry(2.2, 0.56, 192, 24);
    const knotMaterial = new MeshStandardMaterial({
        color: new Color("#8d94ff"),
        roughness: 0.26,
        metalness: 0.76,
        emissive: new Color("#27206d"),
        emissiveIntensity: 0.4,
        wireframe: true,
    });
    const knotMesh = new Mesh(knotGeometry, knotMaterial);
    geometryCluster.add(knotMesh);

    const coreGeometry = new IcosahedronGeometry(1.05, 2);
    const coreMaterial = new MeshStandardMaterial({
        color: new Color("#72f3dc"),
        roughness: 0.12,
        metalness: 0.62,
        emissive: new Color("#1d7f7d"),
        emissiveIntensity: 0.35,
        transparent: true,
        opacity: 0.82,
    });
    const coreMesh = new Mesh(coreGeometry, coreMaterial);
    geometryCluster.add(coreMesh);

    const particleCount = 300;
    const particlePositions = new Float32Array(particleCount * 3);
    for (let index = 0; index < particleCount; index += 1) {
        const radius = 3.8 + Math.random() * 3.6;
        const theta = Math.random() * Math.PI * 2;
        const phi = Math.acos(2 * Math.random() - 1);
        const sinPhi = Math.sin(phi);
        const baseOffset = index * 3;
        particlePositions[baseOffset] = radius * sinPhi * Math.cos(theta);
        particlePositions[baseOffset + 1] = radius * sinPhi * Math.sin(theta);
        particlePositions[baseOffset + 2] = radius * Math.cos(phi);
    }

    const particleGeometry = new BufferGeometry();
    particleGeometry.setAttribute(
        "position",
        new BufferAttribute(particlePositions, 3),
    );
    const particleMaterial = new PointsMaterial({
        size: 0.034,
        color: new Color("#9ea5ff"),
        transparent: true,
        opacity: 0.78,
    });
    const particleField = new Points(particleGeometry, particleMaterial);
    scene.add(particleField);

    const pointerTarget = { x: 0, y: 0 };
    const pointerCurrent = { x: 0, y: 0 };

    const resizeRenderer = () => {
        const nextWidth = Math.max(container.clientWidth, 320);
        const nextHeight = Math.max(container.clientHeight, 280);
        renderer.setSize(nextWidth, nextHeight, false);
        camera.aspect = nextWidth / nextHeight;
        camera.updateProjectionMatrix();
    };

    const handlePointerMove = (event: PointerEvent) => {
        const bounds = container.getBoundingClientRect();
        const normalizedX = (event.clientX - bounds.left) / bounds.width;
        const normalizedY = (event.clientY - bounds.top) / bounds.height;
        pointerTarget.x = (normalizedX - 0.5) * 2;
        pointerTarget.y = (normalizedY - 0.5) * -2;
    };

    const handlePointerLeave = () => {
        pointerTarget.x = 0;
        pointerTarget.y = 0;
    };

    container.addEventListener("pointermove", handlePointerMove);
    container.addEventListener("pointerleave", handlePointerLeave);

    const resizeObserver = new ResizeObserver(() => {
        resizeRenderer();
    });
    resizeObserver.observe(container);
    window.addEventListener("resize", resizeRenderer);

    resizeRenderer();

    const clock = new Clock();
    let animationFrameHandle = 0;
    let isDestroyed = false;

    const renderLoop = () => {
        if (isDestroyed) {
            return;
        }

        const elapsedTime = clock.getElapsedTime();
        pointerCurrent.x = lerp(pointerCurrent.x, pointerTarget.x, 0.045);
        pointerCurrent.y = lerp(pointerCurrent.y, pointerTarget.y, 0.045);

        knotMesh.rotation.x = elapsedTime * 0.18 + pointerCurrent.y * 0.21;
        knotMesh.rotation.y = elapsedTime * 0.28 + pointerCurrent.x * 0.36;
        coreMesh.rotation.y = elapsedTime * 0.42;
        coreMesh.rotation.z = elapsedTime * 0.18;

        particleField.rotation.y = -elapsedTime * 0.04;
        particleField.rotation.x = Math.sin(elapsedTime * 0.15) * 0.08;

        geometryCluster.position.x = pointerCurrent.x * 0.44;
        geometryCluster.position.y = pointerCurrent.y * 0.36;

        camera.position.x = pointerCurrent.x * 0.58;
        camera.position.y = pointerCurrent.y * 0.46;
        camera.lookAt(0, 0, 0);

        renderer.render(scene, camera);
        animationFrameHandle = window.requestAnimationFrame(renderLoop);
    };

    renderLoop();

    return () => {
        isDestroyed = true;
        window.cancelAnimationFrame(animationFrameHandle);
        resizeObserver.disconnect();
        window.removeEventListener("resize", resizeRenderer);
        container.removeEventListener("pointermove", handlePointerMove);
        container.removeEventListener("pointerleave", handlePointerLeave);

        knotGeometry.dispose();
        knotMaterial.dispose();
        coreGeometry.dispose();
        coreMaterial.dispose();
        particleGeometry.dispose();
        particleMaterial.dispose();
        renderer.dispose();
    };
}

function applyMotionPreference(): void {
    const prefersReducedMotion = mediaQueryList?.matches ?? false;
    reducedMotionEnabled.value = prefersReducedMotion;

    releaseScene?.();
    releaseScene = null;

    if (!prefersReducedMotion) {
        releaseScene = mountSignalFieldScene();
    }
}

onMounted(() => {
    mediaQueryList = window.matchMedia("(prefers-reduced-motion: reduce)");
    mediaQueryListener = () => {
        applyMotionPreference();
    };

    if (typeof mediaQueryList.addEventListener === "function") {
        mediaQueryList.addEventListener("change", mediaQueryListener);
    } else {
        mediaQueryList.addListener(mediaQueryListener);
    }

    applyMotionPreference();
});

onBeforeUnmount(() => {
    releaseScene?.();
    releaseScene = null;

    if (mediaQueryList && mediaQueryListener) {
        if (typeof mediaQueryList.removeEventListener === "function") {
            mediaQueryList.removeEventListener("change", mediaQueryListener);
        } else {
            mediaQueryList.removeListener(mediaQueryListener);
        }
    }

    mediaQueryList = null;
    mediaQueryListener = null;
});
</script>

<template>
    <div
        ref="containerElement"
        class="signal-field-scene"
        aria-hidden="true"
        :data-reduced-motion="reducedMotionEnabled ? 'true' : 'false'"
    >
        <canvas ref="canvasElement" class="signal-field-scene__canvas" />
        <div class="signal-field-scene__grain-layer" />
        <div
            class="signal-field-scene__reduced-motion-artwork"
            :data-visible="reducedMotionEnabled ? 'true' : 'false'"
        />
    </div>
</template>

<style scoped>
.signal-field-scene {
    position: relative;
    width: 100%;
    min-height: 18rem;
    overflow: hidden;
    border-radius: 1.5rem;
    border: 1px solid rgba(122, 140, 255, 0.35);
    background:
        radial-gradient(circle at 20% 20%, rgba(77, 61, 153, 0.55), transparent 55%),
        radial-gradient(circle at 80% 80%, rgba(40, 120, 142, 0.42), transparent 60%),
        linear-gradient(155deg, #05070f 15%, #0c1120 58%, #101933 100%);
    box-shadow:
        0 42px 70px rgba(3, 6, 16, 0.45),
        inset 0 0 0 1px rgba(255, 255, 255, 0.03);
}

.signal-field-scene__canvas {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    display: block;
}

.signal-field-scene__grain-layer {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image: radial-gradient(rgba(255, 255, 255, 0.11) 0.65px, transparent 0.65px);
    background-size: 3px 3px;
    mix-blend-mode: soft-light;
    opacity: 0.22;
}

.signal-field-scene__reduced-motion-artwork {
    position: absolute;
    inset: -14%;
    pointer-events: none;
    opacity: 0;
    transition: opacity 280ms ease;
    background:
        conic-gradient(
            from 220deg at 50% 50%,
            rgba(45, 246, 210, 0.2),
            rgba(136, 112, 255, 0.42),
            rgba(22, 38, 74, 0.4),
            rgba(45, 246, 210, 0.2)
        );
    filter: blur(36px);
}

.signal-field-scene__reduced-motion-artwork[data-visible="true"] {
    opacity: 0.95;
}

.signal-field-scene[data-reduced-motion="true"] .signal-field-scene__canvas {
    opacity: 0;
}
</style>
