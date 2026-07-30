<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from "vue";

const containerRef = ref<HTMLElement | null>(null);
const canvasRef = ref<HTMLCanvasElement | null>(null);
const useFallbackArt = ref(false);

let teardown = () => {};

function motionShouldReduce(): boolean {
    if (
        typeof window === "undefined" ||
        typeof window.matchMedia !== "function"
    ) {
        return false;
    }

    return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
}

onMounted(async () => {
    const container = containerRef.value;
    const canvas = canvasRef.value;
    const isJsdomEnvironment =
        typeof navigator !== "undefined" &&
        navigator.userAgent.toLowerCase().includes("jsdom");

    if (!container || !canvas || motionShouldReduce() || isJsdomEnvironment) {
        useFallbackArt.value = true;
        return;
    }

    try {
        const { createCinematicScene } = await import(
            "@/core/experience/createCinematicScene"
        );
        const sceneController = createCinematicScene(canvas);

        let pointerX = 0;
        let pointerY = 0;

        const handlePointerMove = (event: PointerEvent): void => {
            const bounds = container.getBoundingClientRect();

            if (bounds.width <= 0 || bounds.height <= 0) {
                return;
            }

            const normalizedX = (event.clientX - bounds.left) / bounds.width - 0.5;
            const normalizedY = (event.clientY - bounds.top) / bounds.height - 0.5;
            pointerX = normalizedX * 2;
            pointerY = normalizedY * 2;
        };

        const updateSize = (): void => {
            const width = Math.max(container.clientWidth, 1);
            const height = Math.max(container.clientHeight, 1);

            sceneController.resize(width, height, window.devicePixelRatio || 1);
        };

        let resizeObserver: ResizeObserver | null = null;

        if (typeof ResizeObserver !== "undefined") {
            resizeObserver = new ResizeObserver(updateSize);
            resizeObserver.observe(container);
        } else {
            window.addEventListener("resize", updateSize);
        }

        window.addEventListener("pointermove", handlePointerMove, { passive: true });
        updateSize();

        const startAt = performance.now();
        let frameId = 0;

        const animate = (now: number): void => {
            const elapsed = (now - startAt) * 0.001;

            sceneController.render(elapsed, pointerX, pointerY);
            frameId = window.requestAnimationFrame(animate);
        };

        frameId = window.requestAnimationFrame(animate);

        teardown = () => {
            window.cancelAnimationFrame(frameId);
            window.removeEventListener("pointermove", handlePointerMove);
            window.removeEventListener("resize", updateSize);
            resizeObserver?.disconnect();
            sceneController.dispose();
        };
    } catch {
        useFallbackArt.value = true;
    }
});

onBeforeUnmount(() => {
    teardown();
});
</script>

<template>
    <div ref="containerRef" class="cinematic-orb-scene">
        <canvas
            v-show="!useFallbackArt"
            ref="canvasRef"
            class="cinematic-orb-scene__canvas"
            aria-hidden="true"
        />
        <div v-if="useFallbackArt" class="cinematic-orb-scene__fallback" aria-hidden="true" />
        <p class="sr-only">
            Interactive 3D scene previewing technical craftsmanship and motion fidelity.
        </p>
    </div>
</template>

<style scoped>
.cinematic-orb-scene {
    position: relative;
    min-height: 17rem;
    width: 100%;
    border: 1px solid color-mix(in srgb, var(--color-border) 74%, transparent);
    border-radius: 1.25rem;
    overflow: hidden;
    background:
        radial-gradient(circle at 30% 28%, rgba(152, 193, 255, 0.22), transparent 55%),
        radial-gradient(circle at 72% 72%, rgba(255, 205, 130, 0.14), transparent 54%),
        linear-gradient(145deg, rgba(13, 17, 24, 0.96), rgba(17, 24, 36, 0.98));
}

.cinematic-orb-scene__canvas,
.cinematic-orb-scene__fallback {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

.cinematic-orb-scene__canvas {
    display: block;
}

.cinematic-orb-scene__fallback {
    background:
        radial-gradient(circle at 50% 50%, rgba(109, 154, 255, 0.58), transparent 46%),
        radial-gradient(circle at 70% 28%, rgba(255, 204, 126, 0.4), transparent 34%),
        repeating-radial-gradient(
            circle at 52% 54%,
            rgba(255, 255, 255, 0.06) 0 2px,
            transparent 2px 11px
        );
}
</style>
