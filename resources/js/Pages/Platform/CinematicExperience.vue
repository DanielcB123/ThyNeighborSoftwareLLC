<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";
import SurfaceShell from "@/layouts/shells/SurfaceShell.vue";
import SignalFieldScene from "@/pages/platform/components/SignalFieldScene.vue";

interface HeroAction {
    label: string;
    path: string;
}

interface HeroContent {
    eyebrow: string;
    title: string;
    summary: string;
    chapterLabel: string;
    chapterLead: string;
    primaryAction: HeroAction;
    secondaryAction: HeroAction;
}

interface FeaturedProject {
    id: string;
    title: string;
    sector: string;
    summary: string;
    impact: string;
    spotlight: string;
    accentFrom: string;
    accentTo: string;
}

interface CapabilityTrack {
    id: string;
    label: string;
    title: string;
    details: readonly string[];
}

interface PlatformSurface {
    id: string;
    surface: string;
    title: string;
    description: string;
    outcomes: readonly string[];
}

interface DeliveryPhase {
    id: string;
    phase: string;
    title: string;
    details: string;
}

interface ClosingCta {
    heading: string;
    description: string;
    primaryAction: HeroAction;
    secondaryAction: HeroAction;
}

const props = defineProps<{
    chapter: string;
    meta: {
        title: string;
        description: string;
    };
    hero: HeroContent;
    featuredProjects: readonly FeaturedProject[];
    capabilityTracks: readonly CapabilityTrack[];
    platformSurfaces: readonly PlatformSurface[];
    deliveryPhases: readonly DeliveryPhase[];
    closingCta: ClosingCta;
}>();

const pageTitle = computed(
    () =>
        `${props.hero.chapterLabel}: ${props.hero.title}`,
);

function projectCardStyle(project: FeaturedProject): Record<string, string> {
    return {
        "--project-accent-from": project.accentFrom,
        "--project-accent-to": project.accentTo,
    };
}
</script>

<template>
    <Head :title="props.meta.title" />

    <SurfaceShell
        :page-title="pageTitle"
        :page-summary="props.meta.description"
    >
        <article class="cinematic-experience">
            <section class="cinematic-experience__hero">
                <div class="cinematic-experience__hero-copy">
                    <p class="cinematic-experience__eyebrow">
                        {{ props.hero.eyebrow }}
                    </p>
                    <h1 class="cinematic-experience__hero-title">
                        {{ props.hero.title }}
                    </h1>
                    <p class="cinematic-experience__hero-summary">
                        {{ props.hero.summary }}
                    </p>
                    <p class="cinematic-experience__chapter-lead">
                        <span class="cinematic-experience__chapter-label">
                            {{ props.hero.chapterLabel }}
                        </span>
                        {{ props.hero.chapterLead }}
                    </p>
                    <div class="cinematic-experience__hero-actions">
                        <a
                            :href="props.hero.primaryAction.path"
                            class="cinematic-experience__hero-action cinematic-experience__hero-action--primary"
                        >
                            {{ props.hero.primaryAction.label }}
                        </a>
                        <a
                            :href="props.hero.secondaryAction.path"
                            class="cinematic-experience__hero-action cinematic-experience__hero-action--secondary"
                        >
                            {{ props.hero.secondaryAction.label }}
                        </a>
                    </div>
                </div>

                <div class="cinematic-experience__hero-scene">
                    <SignalFieldScene />
                </div>
            </section>

            <section class="cinematic-experience__section">
                <header class="cinematic-experience__section-header">
                    <p class="cinematic-experience__section-eyebrow">
                        Selected build narratives
                    </p>
                    <h2 class="cinematic-experience__section-title">
                        Projects that connect visual ambition to operational outcomes
                    </h2>
                </header>
                <ul class="cinematic-experience__project-grid">
                    <li
                        v-for="project in props.featuredProjects"
                        :key="project.id"
                        class="cinematic-experience__project-card"
                        :style="projectCardStyle(project)"
                    >
                        <p class="cinematic-experience__project-sector">
                            {{ project.sector }}
                        </p>
                        <h3 class="cinematic-experience__project-title">
                            {{ project.title }}
                        </h3>
                        <p class="cinematic-experience__project-summary">
                            {{ project.summary }}
                        </p>
                        <p class="cinematic-experience__project-impact">
                            {{ project.impact }}
                        </p>
                        <p class="cinematic-experience__project-spotlight">
                            {{ project.spotlight }}
                        </p>
                    </li>
                </ul>
            </section>

            <section class="cinematic-experience__section">
                <header class="cinematic-experience__section-header">
                    <p class="cinematic-experience__section-eyebrow">
                        Capability tracks
                    </p>
                    <h2 class="cinematic-experience__section-title">
                        Full-stack delivery from identity systems to enterprise runtime controls
                    </h2>
                </header>
                <ul class="cinematic-experience__track-grid">
                    <li
                        v-for="track in props.capabilityTracks"
                        :key="track.id"
                        class="cinematic-experience__track-card"
                    >
                        <p class="cinematic-experience__track-label">
                            {{ track.label }}
                        </p>
                        <h3 class="cinematic-experience__track-title">
                            {{ track.title }}
                        </h3>
                        <ul class="cinematic-experience__track-list">
                            <li
                                v-for="detail in track.details"
                                :key="detail"
                                class="cinematic-experience__track-list-item"
                            >
                                {{ detail }}
                            </li>
                        </ul>
                    </li>
                </ul>
            </section>

            <section class="cinematic-experience__section">
                <header class="cinematic-experience__section-header">
                    <p class="cinematic-experience__section-eyebrow">
                        Surface architecture
                    </p>
                    <h2 class="cinematic-experience__section-title">
                        Public experiences, secure portals, and operational control surfaces
                    </h2>
                </header>
                <ul class="cinematic-experience__surface-grid">
                    <li
                        v-for="surface in props.platformSurfaces"
                        :key="surface.id"
                        class="cinematic-experience__surface-card"
                    >
                        <p class="cinematic-experience__surface-tag">
                            {{ surface.surface }}
                        </p>
                        <h3 class="cinematic-experience__surface-title">
                            {{ surface.title }}
                        </h3>
                        <p class="cinematic-experience__surface-description">
                            {{ surface.description }}
                        </p>
                        <ul class="cinematic-experience__surface-outcomes">
                            <li
                                v-for="outcome in surface.outcomes"
                                :key="outcome"
                                class="cinematic-experience__surface-outcome"
                            >
                                {{ outcome }}
                            </li>
                        </ul>
                    </li>
                </ul>
            </section>

            <section class="cinematic-experience__section">
                <header class="cinematic-experience__section-header">
                    <p class="cinematic-experience__section-eyebrow">
                        Delivery method
                    </p>
                    <h2 class="cinematic-experience__section-title">
                        A measured production cadence built for momentum and confidence
                    </h2>
                </header>
                <ol class="cinematic-experience__timeline">
                    <li
                        v-for="phase in props.deliveryPhases"
                        :key="phase.id"
                        class="cinematic-experience__timeline-item"
                    >
                        <p class="cinematic-experience__timeline-phase">
                            {{ phase.phase }}
                        </p>
                        <h3 class="cinematic-experience__timeline-title">
                            {{ phase.title }}
                        </h3>
                        <p class="cinematic-experience__timeline-details">
                            {{ phase.details }}
                        </p>
                    </li>
                </ol>
            </section>

            <section class="cinematic-experience__closing">
                <h2 class="cinematic-experience__closing-title">
                    {{ props.closingCta.heading }}
                </h2>
                <p class="cinematic-experience__closing-description">
                    {{ props.closingCta.description }}
                </p>
                <div class="cinematic-experience__closing-actions">
                    <a
                        :href="props.closingCta.primaryAction.path"
                        class="cinematic-experience__hero-action cinematic-experience__hero-action--primary"
                    >
                        {{ props.closingCta.primaryAction.label }}
                    </a>
                    <a
                        :href="props.closingCta.secondaryAction.path"
                        class="cinematic-experience__hero-action cinematic-experience__hero-action--secondary"
                    >
                        {{ props.closingCta.secondaryAction.label }}
                    </a>
                </div>
            </section>
        </article>
    </SurfaceShell>
</template>

<style scoped>
.cinematic-experience {
    display: grid;
    gap: clamp(3rem, 4.5vw, 5.5rem);
    padding-bottom: clamp(4rem, 10vw, 7rem);
}

.cinematic-experience__hero {
    display: grid;
    gap: clamp(1.5rem, 4vw, 3rem);
    grid-template-columns: repeat(12, minmax(0, 1fr));
    align-items: stretch;
}

.cinematic-experience__hero-copy {
    grid-column: span 12;
    display: grid;
    gap: 1.15rem;
    align-content: start;
}

.cinematic-experience__hero-scene {
    grid-column: span 12;
    min-height: 22rem;
}

@media (min-width: 980px) {
    .cinematic-experience__hero-copy {
        grid-column: span 6;
    }

    .cinematic-experience__hero-scene {
        grid-column: span 6;
    }
}

.cinematic-experience__eyebrow,
.cinematic-experience__section-eyebrow,
.cinematic-experience__chapter-label,
.cinematic-experience__project-sector,
.cinematic-experience__track-label,
.cinematic-experience__surface-tag,
.cinematic-experience__timeline-phase {
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.72rem;
    font-weight: 600;
    color: rgba(202, 211, 255, 0.84);
}

.cinematic-experience__hero-title {
    max-width: 15ch;
    margin: 0;
    color: #f3f6ff;
    font-size: clamp(2.2rem, 4.3vw, 4.15rem);
    line-height: 1.03;
    text-wrap: balance;
}

.cinematic-experience__hero-summary,
.cinematic-experience__chapter-lead,
.cinematic-experience__section-header,
.cinematic-experience__project-summary,
.cinematic-experience__project-impact,
.cinematic-experience__project-spotlight,
.cinematic-experience__surface-description,
.cinematic-experience__timeline-details,
.cinematic-experience__closing-description {
    color: rgba(208, 214, 243, 0.82);
}

.cinematic-experience__hero-summary {
    max-width: 58ch;
    margin: 0;
    font-size: 1.04rem;
    line-height: 1.68;
}

.cinematic-experience__chapter-lead {
    display: grid;
    gap: 0.3rem;
    margin: 0;
    max-width: 52ch;
    font-size: 0.96rem;
    line-height: 1.62;
}

.cinematic-experience__hero-actions,
.cinematic-experience__closing-actions {
    margin-top: 0.55rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.cinematic-experience__hero-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 2.75rem;
    padding-inline: 1.12rem;
    border-radius: 999px;
    font-size: 0.86rem;
    font-weight: 600;
    text-decoration: none;
    transition:
        transform 220ms ease,
        border-color 220ms ease,
        background-color 220ms ease,
        color 220ms ease;
}

.cinematic-experience__hero-action:hover,
.cinematic-experience__hero-action:focus-visible {
    transform: translateY(-1px);
}

.cinematic-experience__hero-action--primary {
    background: linear-gradient(95deg, #8d94ff 0%, #68ebd6 100%);
    color: #0a1023;
}

.cinematic-experience__hero-action--secondary {
    border: 1px solid rgba(151, 164, 255, 0.52);
    color: #e3e8ff;
    background: rgba(17, 23, 44, 0.42);
}

.cinematic-experience__section {
    display: grid;
    gap: 1.55rem;
}

.cinematic-experience__section-header {
    display: grid;
    gap: 0.75rem;
    margin: 0;
    max-width: 66ch;
}

.cinematic-experience__section-title {
    margin: 0;
    font-size: clamp(1.45rem, 2.7vw, 2.35rem);
    line-height: 1.18;
    color: #eef2ff;
    text-wrap: balance;
}

.cinematic-experience__project-grid,
.cinematic-experience__track-grid,
.cinematic-experience__surface-grid {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(15.5rem, 1fr));
}

.cinematic-experience__project-card,
.cinematic-experience__track-card,
.cinematic-experience__surface-card,
.cinematic-experience__timeline-item,
.cinematic-experience__closing {
    border-radius: 1.2rem;
    border: 1px solid rgba(117, 133, 229, 0.34);
    background: rgba(15, 20, 39, 0.72);
    box-shadow:
        0 28px 52px rgba(3, 7, 18, 0.35),
        inset 0 0 0 1px rgba(255, 255, 255, 0.03);
}

.cinematic-experience__project-card {
    display: grid;
    gap: 0.82rem;
    padding: 1.2rem;
    background:
        linear-gradient(
            140deg,
            color-mix(in srgb, var(--project-accent-from) 24%, rgba(15, 20, 39, 0.72)),
            color-mix(in srgb, var(--project-accent-to) 24%, rgba(15, 20, 39, 0.72))
        ),
        rgba(15, 20, 39, 0.72);
}

.cinematic-experience__project-title,
.cinematic-experience__track-title,
.cinematic-experience__surface-title,
.cinematic-experience__timeline-title,
.cinematic-experience__closing-title {
    margin: 0;
    color: #edf1ff;
    line-height: 1.23;
}

.cinematic-experience__project-title {
    font-size: 1.2rem;
}

.cinematic-experience__project-summary,
.cinematic-experience__project-impact,
.cinematic-experience__project-spotlight,
.cinematic-experience__surface-description,
.cinematic-experience__timeline-details {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.56;
}

.cinematic-experience__project-impact {
    color: rgba(232, 237, 255, 0.94);
    font-weight: 500;
}

.cinematic-experience__project-spotlight {
    color: rgba(196, 205, 240, 0.95);
}

.cinematic-experience__track-card,
.cinematic-experience__surface-card {
    display: grid;
    gap: 0.84rem;
    padding: 1.2rem;
}

.cinematic-experience__track-title,
.cinematic-experience__surface-title,
.cinematic-experience__timeline-title {
    font-size: 1.08rem;
}

.cinematic-experience__track-list,
.cinematic-experience__surface-outcomes,
.cinematic-experience__timeline {
    list-style: none;
    margin: 0;
    padding: 0;
}

.cinematic-experience__track-list {
    display: grid;
    gap: 0.64rem;
}

.cinematic-experience__track-list-item,
.cinematic-experience__surface-outcome {
    position: relative;
    padding-left: 0.92rem;
    color: rgba(210, 218, 249, 0.88);
    font-size: 0.9rem;
    line-height: 1.55;
}

.cinematic-experience__track-list-item::before,
.cinematic-experience__surface-outcome::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0.62rem;
    width: 0.32rem;
    height: 0.32rem;
    border-radius: 999px;
    background: rgba(118, 235, 216, 0.95);
}

.cinematic-experience__surface-outcomes {
    display: grid;
    gap: 0.5rem;
}

.cinematic-experience__timeline {
    display: grid;
    gap: 0.9rem;
    grid-template-columns: repeat(auto-fit, minmax(14.5rem, 1fr));
}

.cinematic-experience__timeline-item {
    display: grid;
    gap: 0.76rem;
    padding: 1.1rem;
}

.cinematic-experience__closing {
    padding: clamp(1.45rem, 3vw, 2rem);
    display: grid;
    gap: 0.9rem;
    background:
        radial-gradient(circle at 14% 22%, rgba(107, 128, 255, 0.24), transparent 60%),
        radial-gradient(circle at 90% 78%, rgba(42, 216, 196, 0.2), transparent 62%),
        rgba(12, 17, 32, 0.78);
}

.cinematic-experience__closing-title {
    font-size: clamp(1.42rem, 2.45vw, 2.2rem);
}

.cinematic-experience__closing-description {
    margin: 0;
    max-width: 60ch;
    font-size: 0.98rem;
    line-height: 1.6;
}

@media (prefers-reduced-motion: reduce) {
    .cinematic-experience__hero-action {
        transition: none;
    }

    .cinematic-experience__hero-action:hover,
    .cinematic-experience__hero-action:focus-visible {
        transform: none;
    }
}
</style>
