<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from "vue";

const containerRef = ref<HTMLElement | null>(null);
const canvasRef = ref<HTMLCanvasElement | null>(null);
const useFallbackArt = ref(false);

let teardown = () => {};

function motionShouldReduce(): boolean {
    if (typeof window === "undefined" || !("matchMedia" in window)) {
        return false;
    }

    return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
}

onMounted(async () => {
    const container = containerRef.value;
    const canvas = canvasRef.value;

    if (!container || !canvas || motionShouldReduce()) {
        useFallbackArt.value = true;
        return;
    }

    try {
        const THREE = await import("three");
        const renderer = new THREE.WebGLRenderer({
            canvas,
            antialias: true,
            alpha: true,
            powerPreference: "high-performance",
        });
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(34, 1, 0.1, 40);
        camera.position.set(0, 0, 5.25);

        renderer.outputColorSpace = THREE.SRGBColorSpace;
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1.05;

        const keyLight = new THREE.DirectionalLight(0x9ec5ff, 2.1);
        keyLight.position.set(2.8, 2.5, 2.6);
        scene.add(keyLight);

        const rimLight = new THREE.PointLight(0xffc477, 1.6, 12);
        rimLight.position.set(-2.4, -1.6, 2.8);
        scene.add(rimLight);

        const ambientLight = new THREE.AmbientLight(0xbec8dc, 0.9);
        scene.add(ambientLight);

        const coreGeometry = new THREE.IcosahedronGeometry(1.12, 2);
        const coreMaterial = new THREE.MeshStandardMaterial({
            color: 0x5e89f7,
            emissive: 0x101f46,
            emissiveIntensity: 0.7,
            metalness: 0.32,
            roughness: 0.29,
        });
        const coreMesh = new THREE.Mesh(coreGeometry, coreMaterial);
        scene.add(coreMesh);

        const shellGeometry = new THREE.TorusKnotGeometry(1.88, 0.065, 210, 32, 2, 5);
        const shellMaterial = new THREE.MeshStandardMaterial({
            color: 0xffd181,
            emissive: 0x49331b,
            emissiveIntensity: 0.5,
            metalness: 0.74,
            roughness: 0.24,
        });
        const shellMesh = new THREE.Mesh(shellGeometry, shellMaterial);
        shellMesh.rotation.x = Math.PI / 3;
        scene.add(shellMesh);

        const haloGeometry = new THREE.TorusGeometry(2.2, 0.03, 16, 180);
        const haloMaterial = new THREE.MeshBasicMaterial({
            color: 0x97bbff,
            transparent: true,
            opacity: 0.62,
        });
        const haloMesh = new THREE.Mesh(haloGeometry, haloMaterial);
        haloMesh.rotation.x = Math.PI / 2.45;
        scene.add(haloMesh);

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

            renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
            renderer.setSize(width, height, false);
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
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

            coreMesh.rotation.y += 0.0034;
            coreMesh.rotation.x = 0.24 + Math.sin(elapsed * 0.8) * 0.18 + pointerY * 0.07;
            coreMesh.position.x = pointerX * 0.18;
            coreMesh.position.y = Math.sin(elapsed * 1.1) * 0.08;

            shellMesh.rotation.y -= 0.0026;
            shellMesh.rotation.z += 0.0022;
            shellMesh.rotation.x = Math.PI / 3 + pointerY * 0.09;

            haloMesh.rotation.z += 0.0018;
            haloMesh.rotation.x = Math.PI / 2.45 + pointerY * 0.1;
            haloMesh.position.x = pointerX * 0.12;

            renderer.render(scene, camera);
            frameId = window.requestAnimationFrame(animate);
        };

        frameId = window.requestAnimationFrame(animate);

        teardown = () => {
            window.cancelAnimationFrame(frameId);
            window.removeEventListener("pointermove", handlePointerMove);
            window.removeEventListener("resize", updateSize);
            resizeObserver?.disconnect();

            coreGeometry.dispose();
            shellGeometry.dispose();
            haloGeometry.dispose();
            coreMaterial.dispose();
            shellMaterial.dispose();
            haloMaterial.dispose();
            renderer.dispose();
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
