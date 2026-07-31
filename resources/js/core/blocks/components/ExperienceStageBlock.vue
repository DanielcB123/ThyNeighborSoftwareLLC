<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import type { ExperienceStageBlockData } from "@/core/blocks/types";

interface SignalNode {
    x: number;
    y: number;
    depth: number;
    speed: number;
    phase: number;
    radius: number;
}

const props = defineProps<{
    blockId: string;
    data: ExperienceStageBlockData;
}>();

const stageElement = ref<HTMLElement | null>(null);
const canvasElement = ref<HTMLCanvasElement | null>(null);
const pointerTargetX = ref(0);
const pointerTargetY = ref(0);
const pointerCurrentX = ref(0);
const pointerCurrentY = ref(0);

const stageTransform = computed(() => ({
    transform: `perspective(1400px) rotateX(${pointerCurrentY.value * 7}deg) rotateY(${pointerCurrentX.value * -11}deg)`,
}));

let animationFrameId = 0;
let resizeObserver: ResizeObserver | null = null;

function handlePointerMove(event: PointerEvent) {
    const stage = stageElement.value;
    if (!stage) {
        return;
    }

    const rect = stage.getBoundingClientRect();
    const relativeX = (event.clientX - rect.left) / rect.width;
    const relativeY = (event.clientY - rect.top) / rect.height;

    pointerTargetX.value = Math.min(1, Math.max(-1, (relativeX - 0.5) * 2));
    pointerTargetY.value = Math.min(1, Math.max(-1, (relativeY - 0.5) * 2));
}

function handlePointerLeave() {
    pointerTargetX.value = 0;
    pointerTargetY.value = 0;
}

onMounted(() => {
    const stage = stageElement.value;
    const canvas = canvasElement.value;

    if (!stage || !canvas) {
        return;
    }

    const context = canvas.getContext("2d");

    if (!context) {
        return;
    }

    const prefersReducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;

    const nodes: SignalNode[] = Array.from({ length: 42 }, (_, index) => {
        const angle = (index / 42) * Math.PI * 2;
        const ringScale = 0.35 + ((index % 7) / 7) * 0.75;

        return {
            x: Math.cos(angle) * ringScale,
            y: Math.sin(angle * 1.7) * ringScale,
            depth: ((index % 6) - 3) / 3,
            speed: 0.3 + (index % 5) * 0.13,
            phase: index * 0.47,
            radius: 1.4 + (index % 3),
        };
    });

    const connectionDistance = 66;
    const palette = {
        strokeA: "24, 103, 106",
        strokeB: "159, 84, 46",
        node: "rgba(12, 25, 42, 0.9)",
        glow: "rgba(79, 140, 255, 0.48)",
    };

    let width = 0;
    let height = 0;
    let pixelRatio = 1;

    const resize = () => {
        const rect = canvas.getBoundingClientRect();
        width = Math.max(1, rect.width);
        height = Math.max(1, rect.height);
        pixelRatio = Math.min(window.devicePixelRatio || 1, 2);

        canvas.width = Math.max(1, Math.floor(width * pixelRatio));
        canvas.height = Math.max(1, Math.floor(height * pixelRatio));

        context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
    };

    const drawFrame = (timestamp: number) => {
        pointerCurrentX.value += (pointerTargetX.value - pointerCurrentX.value) * 0.08;
        pointerCurrentY.value += (pointerTargetY.value - pointerCurrentY.value) * 0.08;

        const time = timestamp * 0.0007;
        const centerX = width / 2;
        const centerY = height / 2;
        const positions: Array<{ x: number; y: number; depth: number; radius: number }> =
            [];

        context.clearRect(0, 0, width, height);

        const backgroundGradient = context.createRadialGradient(
            centerX + pointerCurrentX.value * 50,
            centerY - pointerCurrentY.value * 42,
            10,
            centerX,
            centerY,
            Math.max(width, height) * 0.7,
        );
        backgroundGradient.addColorStop(0, "rgba(102, 146, 255, 0.18)");
        backgroundGradient.addColorStop(0.5, "rgba(30, 94, 92, 0.14)");
        backgroundGradient.addColorStop(1, "rgba(12, 20, 30, 0)");

        context.fillStyle = backgroundGradient;
        context.fillRect(0, 0, width, height);

        for (const node of nodes) {
            const oscillation = Math.sin(time * node.speed + node.phase);
            const depthShift = node.depth + oscillation * 0.58;
            const perspectiveScale = 1 + depthShift * 0.26;

            const x =
                centerX +
                node.x * width * 0.34 * perspectiveScale +
                pointerCurrentX.value * 24 * perspectiveScale;
            const y =
                centerY +
                node.y * height * 0.31 * perspectiveScale +
                pointerCurrentY.value * 18 * perspectiveScale;

            positions.push({
                x,
                y,
                depth: depthShift,
                radius: node.radius,
            });
        }

        for (let index = 0; index < positions.length; index += 1) {
            const from = positions[index];

            for (
                let comparisonIndex = index + 1;
                comparisonIndex < positions.length;
                comparisonIndex += 1
            ) {
                const to = positions[comparisonIndex];
                const distance = Math.hypot(to.x - from.x, to.y - from.y);

                if (distance > connectionDistance) {
                    continue;
                }

                const alpha = 1 - distance / connectionDistance;
                context.strokeStyle =
                    (index + comparisonIndex) % 3 === 0
                        ? `rgba(${palette.strokeB}, ${Math.max(alpha * 0.35, 0.05)})`
                        : `rgba(${palette.strokeA}, ${Math.max(alpha * 0.42, 0.07)})`;
                context.lineWidth = 0.8 + alpha * 0.7;
                context.beginPath();
                context.moveTo(from.x, from.y);
                context.lineTo(to.x, to.y);
                context.stroke();
            }
        }

        for (const position of positions) {
            const nodeRadius = position.radius + Math.max(position.depth, -0.2) * 0.8;
            context.beginPath();
            context.fillStyle = palette.node;
            context.arc(position.x, position.y, Math.max(0.8, nodeRadius), 0, Math.PI * 2);
            context.fill();

            context.beginPath();
            context.fillStyle = palette.glow;
            context.arc(
                position.x,
                position.y,
                Math.max(1.4, nodeRadius * 1.9),
                0,
                Math.PI * 2,
            );
            context.fill();
        }

        if (!prefersReducedMotion) {
            animationFrameId = window.requestAnimationFrame(drawFrame);
        }
    };

    resize();

    if (prefersReducedMotion) {
        drawFrame(0);
    } else {
        animationFrameId = window.requestAnimationFrame(drawFrame);
    }

    resizeObserver = new ResizeObserver(() => {
        resize();
    });
    resizeObserver.observe(stage);

    stage.addEventListener("pointermove", handlePointerMove);
    stage.addEventListener("pointerleave", handlePointerLeave);
});

onBeforeUnmount(() => {
    if (animationFrameId !== 0) {
        window.cancelAnimationFrame(animationFrameId);
        animationFrameId = 0;
    }

    if (resizeObserver) {
        resizeObserver.disconnect();
        resizeObserver = null;
    }

    stageElement.value?.removeEventListener("pointermove", handlePointerMove);
    stageElement.value?.removeEventListener("pointerleave", handlePointerLeave);
});
</script>

<template>
    <section :id="`block-${blockId}`" class="wb-card wb-card--immersive p-8 lg:p-10">
        <div class="grid gap-7 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:items-center">
            <div class="space-y-5">
                <p class="wb-pill">{{ data.eyebrow }}</p>
                <h2 class="text-3xl font-semibold leading-tight text-[var(--wb-color-text)] md:text-4xl">
                    {{ data.heading }}
                </h2>
                <p class="max-w-2xl text-base leading-7 text-[var(--wb-color-text-muted)]">
                    {{ data.supportingText }}
                </p>
                <ul class="grid gap-3 sm:grid-cols-3">
                    <li
                        v-for="metric in data.metrics"
                        :key="metric.label"
                        class="rounded-[var(--wb-radius-sm)] border border-[var(--wb-color-border)] bg-white/80 p-3"
                    >
                        <p class="text-xs font-semibold uppercase tracking-[0.11em] text-[var(--wb-color-text-muted)]">
                            {{ metric.label }}
                        </p>
                        <p class="mt-2 text-xl font-semibold text-[var(--wb-color-text)]">
                            {{ metric.value }}
                        </p>
                        <p class="mt-1 text-xs text-[var(--wb-color-text-muted)]">
                            {{ metric.detail }}
                        </p>
                    </li>
                </ul>
            </div>

            <div
                ref="stageElement"
                class="relative min-h-[19rem] overflow-hidden rounded-[var(--wb-radius-md)] border border-[var(--wb-color-border-strong)] bg-[linear-gradient(160deg,#f7f8fb_0%,#f0f4f9_55%,#ece8e2_100%)]"
                :style="stageTransform"
            >
                <div class="absolute left-4 top-4 rounded-[var(--wb-radius-xs)] border border-[var(--wb-color-border)] bg-white/80 px-3 py-1 text-xs font-semibold tracking-[0.09em] text-[var(--wb-color-text-muted)]">
                    {{ data.stageLabel }}
                </div>
                <canvas
                    ref="canvasElement"
                    class="absolute inset-0 h-full w-full"
                    aria-hidden="true"
                />
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-white/85 to-transparent" />
            </div>
        </div>
    </section>
</template>
