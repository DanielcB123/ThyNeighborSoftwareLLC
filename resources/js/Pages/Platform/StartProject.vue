<script setup lang="ts">
import axios from "axios";
import { Head } from "@inertiajs/vue3";
import { computed, reactive, ref } from "vue";
import SurfaceShell from "@/layouts/shells/SurfaceShell.vue";

interface PageCopy {
    readonly headTitle?: string;
    readonly title?: string;
    readonly summary?: string;
}

interface ProjectTypeOption {
    value: string;
    title: string;
    description: string;
}

interface OnboardingOptions {
    stepKeys: readonly string[];
    projectTypes: readonly ProjectTypeOption[];
    industries: readonly string[];
    featureOptions: readonly string[];
    assetOptions: readonly string[];
    timelineOptions: readonly string[];
    urgencyOptions: readonly string[];
    budgetOptions: readonly string[];
}

interface OnboardingSnapshot {
    sessionToken: string;
    workspacePublicId: string;
    resumeUrl: string;
    status: string;
    currentStep: number;
    lastSavedAt: string | null;
    responses: Record<string, Record<string, unknown>>;
    capabilities: {
        canEdit: boolean;
        canContinueToScheduling: boolean;
        requiredSteps: string[];
        completedSteps: string[];
    };
}

const props = defineProps<{
    onboarding: OnboardingSnapshot;
    options: OnboardingOptions;
    page?: PageCopy;
}>();

const stepLabels = [
    "Business Information",
    "Project Type",
    "Project Overview",
    "Features & Functionality",
    "Existing Assets",
    "Branding & Design",
    "Timeline",
    "Additional Details",
    "Review",
] as const;

const pageTitle = computed(
    () => props.page?.title ?? "Start your project with a structured intake",
);
const pageSummary = computed(
    () =>
        props.page?.summary ??
        "Give us the context before scheduling your Zoom discovery meeting so our team can prepare.",
);

const session = ref<OnboardingSnapshot>(props.onboarding);
const totalSteps = 9;
const activeStep = ref(Math.min(Math.max(session.value.currentStep, 1), totalSteps));
const saveState = ref<"saved" | "saving" | "error">("saved");
const isCompleting = ref(false);
const validationErrors = ref<Record<string, string[]>>({});

const responses = session.value.responses ?? {};

const form = reactive({
    business: {
        businessName: (responses.business?.businessName as string | undefined) ?? "",
        contactName: (responses.business?.contactName as string | undefined) ?? "",
        businessEmail: (responses.business?.businessEmail as string | undefined) ?? "",
        phone: (responses.business?.phone as string | undefined) ?? "",
        website: (responses.business?.website as string | undefined) ?? "",
        industry: (responses.business?.industry as string | undefined) ?? "",
        businessType: (responses.business?.businessType as string | undefined) ?? "",
        location: (responses.business?.location as string | undefined) ?? "",
        contactRole: (responses.business?.contactRole as string | undefined) ?? "",
    },
    project: {
        projectTypes: (responses.project?.projectTypes as string[] | undefined) ?? [],
        projectTypeOther:
            (responses.project?.projectTypeOther as string | undefined) ?? "",
        projectSummary:
            (responses.project?.projectSummary as string | undefined) ?? "",
    },
    overview: {
        whatToBuild: (responses.overview?.whatToBuild as string | undefined) ?? "",
        problemToSolve:
            (responses.overview?.problemToSolve as string | undefined) ?? "",
        businessGoals:
            (responses.overview?.businessGoals as string | undefined) ?? "",
        primaryUsers:
            (responses.overview?.primaryUsers as string | undefined) ?? "",
    },
    features: {
        requestedFeatures:
            (responses.features?.requestedFeatures as string[] | undefined) ?? [],
        otherFeatures:
            (responses.features?.otherFeatures as string | undefined) ?? "",
    },
    assets: {
        existingAssets:
            (responses.assets?.existingAssets as string[] | undefined) ?? [],
        currentWebsiteUrl:
            (responses.assets?.currentWebsiteUrl as string | undefined) ?? "",
        domainName: (responses.assets?.domainName as string | undefined) ?? "",
        hostingProvider:
            (responses.assets?.hostingProvider as string | undefined) ?? "",
        existingPlatform:
            (responses.assets?.existingPlatform as string | undefined) ?? "",
        existingSoftware:
            (responses.assets?.existingSoftware as string | undefined) ?? "",
        knownIntegrations:
            (responses.assets?.knownIntegrations as string | undefined) ?? "",
        dataNotes: (responses.assets?.dataNotes as string | undefined) ?? "",
    },
    branding: {
        hasLogo: (responses.branding?.hasLogo as boolean | undefined) ?? false,
        hasBrandColors:
            (responses.branding?.hasBrandColors as boolean | undefined) ?? false,
        hasBrandGuidelines:
            (responses.branding?.hasBrandGuidelines as boolean | undefined) ?? false,
        inspirationLinks:
            (responses.branding?.inspirationLinks as string[] | undefined) ?? [""],
        inspirationNotes:
            (responses.branding?.inspirationNotes as string | undefined) ?? "",
        designDirection:
            (responses.branding?.designDirection as string | undefined) ?? "",
    },
    timeline: {
        targetLaunchDate:
            (responses.timeline?.targetLaunchDate as string | undefined) ?? "",
        timelineExpectation:
            (responses.timeline?.timelineExpectation as string | undefined) ?? "",
        urgency: (responses.timeline?.urgency as string | undefined) ?? "",
        deadlineContext:
            (responses.timeline?.deadlineContext as string | undefined) ?? "",
        budgetExpectation:
            (responses.timeline?.budgetExpectation as string | undefined) ?? "",
    },
    additional: {
        additionalNotes:
            (responses.additional?.additionalNotes as string | undefined) ?? "",
    },
});

const saveMessage = computed(() => {
    if (saveState.value === "saving") {
        return "Saving…";
    }
    if (saveState.value === "error") {
        return "Could not save";
    }
    return "Saved";
});

const canContinue = computed(
    () => session.value.capabilities?.canContinueToScheduling ?? false,
);

const selectedProjectLabels = computed(() => {
    const selected = new Set(form.project.projectTypes);
    return props.options.projectTypes
        .filter((option) => selected.has(option.value))
        .map((option) => option.title);
});

function payloadForStep(stepKey: string): Record<string, unknown> {
    if (stepKey === "business") {
        return { ...form.business };
    }
    if (stepKey === "project") {
        return {
            projectTypes: form.project.projectTypes,
            projectTypeOther: form.project.projectTypeOther,
            projectSummary: form.project.projectSummary,
        };
    }
    if (stepKey === "overview") {
        return { ...form.overview };
    }
    if (stepKey === "features") {
        return {
            requestedFeatures: form.features.requestedFeatures,
            otherFeatures: form.features.otherFeatures,
        };
    }
    if (stepKey === "assets") {
        return { ...form.assets };
    }
    if (stepKey === "branding") {
        return {
            ...form.branding,
            inspirationLinks: form.branding.inspirationLinks.filter(
                (link) => link.trim() !== "",
            ),
        };
    }
    if (stepKey === "timeline") {
        return { ...form.timeline };
    }

    return { ...form.additional };
}

function stepKeyForCurrentStep(): string | null {
    if (activeStep.value < 1 || activeStep.value > props.options.stepKeys.length) {
        return null;
    }

    return props.options.stepKeys[activeStep.value - 1] ?? null;
}

function toggleChoice(target: string[], value: string): void {
    if (target.includes(value)) {
        target.splice(target.indexOf(value), 1);
        return;
    }

    target.push(value);
}

function addInspirationLink(): void {
    form.branding.inspirationLinks.push("");
}

function removeInspirationLink(index: number): void {
    form.branding.inspirationLinks.splice(index, 1);
    if (form.branding.inspirationLinks.length === 0) {
        form.branding.inspirationLinks.push("");
    }
}

function applySessionSnapshot(snapshot: OnboardingSnapshot): void {
    session.value = snapshot;
}

async function saveCurrentStep(): Promise<boolean> {
    const stepKey = stepKeyForCurrentStep();
    if (stepKey === null) {
        return true;
    }

    saveState.value = "saving";

    try {
        const response = await axios.post("/start-project/session", {
            sessionToken: session.value.sessionToken,
            stepKey,
            payload: payloadForStep(stepKey),
        });

        validationErrors.value = {};
        applySessionSnapshot(response.data.session as OnboardingSnapshot);
        saveState.value = "saved";
        return true;
    } catch (error: unknown) {
        saveState.value = "error";

        const maybeResponse = (
            error as { response?: { status?: number; data?: { errors?: Record<string, string[]> } } }
        ).response;

        if (maybeResponse?.status === 422 && maybeResponse.data?.errors) {
            validationErrors.value = maybeResponse.data.errors;
        }

        return false;
    }
}

async function nextStep(): Promise<void> {
    const saved = await saveCurrentStep();
    if (!saved) {
        return;
    }

    if (activeStep.value < totalSteps) {
        activeStep.value += 1;
    }
}

function previousStep(): void {
    if (activeStep.value > 1) {
        activeStep.value -= 1;
    }
}

function jumpToStep(stepNumber: number): void {
    activeStep.value = Math.min(Math.max(stepNumber, 1), totalSteps);
}

async function continueToScheduling(): Promise<void> {
    isCompleting.value = true;

    try {
        const response = await axios.post("/start-project/session/complete", {
            sessionToken: session.value.sessionToken,
        });

        window.location.href = response.data.redirectUrl as string;
    } catch (error: unknown) {
        saveState.value = "error";
        const maybeResponse = (
            error as { response?: { status?: number; data?: { errors?: Record<string, string[]> } } }
        ).response;

        if (maybeResponse?.status === 422 && maybeResponse.data?.errors) {
            validationErrors.value = maybeResponse.data.errors;
        }
    } finally {
        isCompleting.value = false;
    }
}
</script>

<template>
    <Head :title="props.page?.headTitle ?? pageTitle" />

    <SurfaceShell :page-title="pageTitle" :page-summary="pageSummary">
        <section class="wb-section-frame grid gap-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="wb-shell-kicker">Step {{ activeStep }} of {{ totalSteps }}</p>
                <p class="wb-pill">{{ saveMessage }}</p>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div class="wb-card p-4">
                    <p class="wb-micro-label text-[var(--color-text-muted)]">Workspace</p>
                    <p class="wb-body-m mt-2 wb-code">{{ session.workspacePublicId }}</p>
                </div>
                <div class="wb-card p-4">
                    <label for="resume-link" class="wb-micro-label text-[var(--color-text-muted)]">
                        Resume Link
                    </label>
                    <input
                        id="resume-link"
                        :value="session.resumeUrl"
                        readonly
                        class="mt-2 w-full rounded-md border border-[var(--color-border)] bg-[var(--color-surface)] px-3 py-2 text-sm"
                    />
                </div>
            </div>

            <ol class="grid gap-2 md:grid-cols-3 xl:grid-cols-5">
                <li
                    v-for="(label, index) in stepLabels"
                    :key="label"
                    class="wb-card p-3"
                    :class="{
                        'border-[color:var(--color-accent)]': index + 1 === activeStep,
                    }"
                >
                    <p class="wb-micro-label text-[var(--color-text-muted)]">
                        Step {{ index + 1 }}
                    </p>
                    <p class="wb-body-s mt-1">{{ label }}</p>
                </li>
            </ol>
        </section>

        <section class="mt-8 grid gap-6">
            <div v-if="activeStep === 1" class="wb-section-frame grid gap-4">
                <h2 class="wb-heading-2">Business Information</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2">
                        <span class="wb-body-s">Business / Organization Name *</span>
                        <input v-model="form.business.businessName" type="text" class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3" />
                    </label>
                    <label class="grid gap-2">
                        <span class="wb-body-s">Contact Name *</span>
                        <input v-model="form.business.contactName" type="text" class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3" />
                    </label>
                    <label class="grid gap-2">
                        <span class="wb-body-s">Email *</span>
                        <input v-model="form.business.businessEmail" type="email" class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3" />
                    </label>
                    <label class="grid gap-2">
                        <span class="wb-body-s">Phone *</span>
                        <input v-model="form.business.phone" type="tel" class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3" />
                    </label>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2">
                        <span class="wb-body-s">Current Website</span>
                        <input v-model="form.business.website" type="url" class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3" />
                    </label>
                    <label class="grid gap-2">
                        <span class="wb-body-s">Industry *</span>
                        <select v-model="form.business.industry" class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3">
                            <option value="">Select industry</option>
                            <option v-for="industry in options.industries" :key="industry" :value="industry">{{ industry }}</option>
                        </select>
                    </label>
                </div>
            </div>

            <div v-if="activeStep === 2" class="wb-section-frame grid gap-4">
                <h2 class="wb-heading-2">What are you looking to build?</h2>
                <div class="grid gap-3 md:grid-cols-2">
                    <button
                        v-for="projectType in options.projectTypes"
                        :key="projectType.value"
                        type="button"
                        class="wb-card p-4 text-left"
                        :class="{
                            'border-[color:var(--color-accent)]': form.project.projectTypes.includes(projectType.value),
                        }"
                        @click="toggleChoice(form.project.projectTypes, projectType.value)"
                    >
                        <p class="wb-heading-3">{{ projectType.title }}</p>
                        <p class="wb-body-s mt-2 text-[var(--color-text-muted)]">
                            {{ projectType.description }}
                        </p>
                    </button>
                </div>
            </div>

            <div v-if="activeStep === 3" class="wb-section-frame grid gap-4">
                <h2 class="wb-heading-2">Project Overview</h2>
                <label class="grid gap-2">
                    <span class="wb-body-s">What would you like us to build? *</span>
                    <textarea v-model="form.overview.whatToBuild" rows="3" class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3" />
                </label>
                <label class="grid gap-2">
                    <span class="wb-body-s">What problem are you trying to solve? *</span>
                    <textarea v-model="form.overview.problemToSolve" rows="3" class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3" />
                </label>
                <label class="grid gap-2">
                    <span class="wb-body-s">What should this project help your business accomplish? *</span>
                    <textarea v-model="form.overview.businessGoals" rows="3" class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3" />
                </label>
                <label class="grid gap-2">
                    <span class="wb-body-s">Who will primarily use it? *</span>
                    <textarea v-model="form.overview.primaryUsers" rows="3" class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3" />
                </label>
            </div>

            <div v-if="activeStep === 4" class="wb-section-frame grid gap-4">
                <h2 class="wb-heading-2">Features and Functionality</h2>
                <div class="grid gap-2 md:grid-cols-2">
                    <button
                        v-for="feature in options.featureOptions"
                        :key="feature"
                        type="button"
                        class="wb-card px-4 py-3 text-left"
                        :class="{
                            'border-[color:var(--color-accent)]': form.features.requestedFeatures.includes(feature),
                        }"
                        @click="toggleChoice(form.features.requestedFeatures, feature)"
                    >
                        <span class="wb-body-s">{{ feature }}</span>
                    </button>
                </div>
            </div>

            <div v-if="activeStep === 5" class="wb-section-frame grid gap-4">
                <h2 class="wb-heading-2">Existing Assets / Technical Context</h2>
                <div class="grid gap-2 md:grid-cols-2">
                    <button
                        v-for="asset in options.assetOptions"
                        :key="asset"
                        type="button"
                        class="wb-card px-4 py-3 text-left"
                        :class="{
                            'border-[color:var(--color-accent)]': form.assets.existingAssets.includes(asset),
                        }"
                        @click="toggleChoice(form.assets.existingAssets, asset)"
                    >
                        <span class="wb-body-s">{{ asset }}</span>
                    </button>
                </div>
            </div>

            <div v-if="activeStep === 6" class="wb-section-frame grid gap-4">
                <h2 class="wb-heading-2">Branding and Design</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    <label class="flex items-center gap-2">
                        <input v-model="form.branding.hasLogo" type="checkbox" />
                        <span class="wb-body-s">We have a logo</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input v-model="form.branding.hasBrandColors" type="checkbox" />
                        <span class="wb-body-s">We have brand colors</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input v-model="form.branding.hasBrandGuidelines" type="checkbox" />
                        <span class="wb-body-s">We have brand guidelines</span>
                    </label>
                </div>
                <div class="grid gap-3">
                    <div class="flex items-center justify-between">
                        <p class="wb-body-s">Websites or apps you like</p>
                        <button type="button" class="wb-button wb-button--ghost" @click="addInspirationLink">
                            Add link
                        </button>
                    </div>
                    <div
                        v-for="(link, index) in form.branding.inspirationLinks"
                        :key="`inspiration-${index}`"
                        class="flex items-center gap-2"
                    >
                        <input v-model="form.branding.inspirationLinks[index]" type="url" class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3" />
                        <button type="button" class="wb-button wb-button--ghost" @click="removeInspirationLink(index)">
                            Remove
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="activeStep === 7" class="wb-section-frame grid gap-4">
                <h2 class="wb-heading-2">Timeline and Context</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2">
                        <span class="wb-body-s">Timeline expectation *</span>
                        <select v-model="form.timeline.timelineExpectation" class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3">
                            <option value="">Select timeline</option>
                            <option v-for="option in options.timelineOptions" :key="option" :value="option">{{ option }}</option>
                        </select>
                    </label>
                    <label class="grid gap-2">
                        <span class="wb-body-s">Urgency *</span>
                        <select v-model="form.timeline.urgency" class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3">
                            <option value="">Select urgency</option>
                            <option v-for="option in options.urgencyOptions" :key="option" :value="option">{{ option }}</option>
                        </select>
                    </label>
                </div>
            </div>

            <div v-if="activeStep === 8" class="wb-section-frame grid gap-4">
                <h2 class="wb-heading-2">Anything else we should know?</h2>
                <textarea v-model="form.additional.additionalNotes" rows="5" class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3" />
            </div>

            <div v-if="activeStep === 9" class="wb-section-frame grid gap-4">
                <h2 class="wb-heading-2">Review</h2>
                <div class="wb-card p-4">
                    <h3 class="wb-heading-3">Business</h3>
                    <p class="wb-body-s mt-2">{{ form.business.businessName || "—" }}</p>
                </div>
                <div class="wb-card p-4">
                    <h3 class="wb-heading-3">Project</h3>
                    <p class="wb-body-s mt-2">{{ selectedProjectLabels.join(", ") || "—" }}</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="wb-button wb-button--ghost" @click="jumpToStep(1)">Edit Business</button>
                    <button type="button" class="wb-button wb-button--ghost" @click="jumpToStep(2)">Edit Project</button>
                    <button type="button" class="wb-button wb-button--ghost" @click="jumpToStep(4)">Edit Features</button>
                </div>
            </div>

            <div v-if="Object.keys(validationErrors).length > 0" class="wb-card p-4">
                <p class="wb-body-s text-[var(--color-danger)]">
                    Please review the highlighted fields and try again.
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <button
                    v-if="activeStep > 1"
                    type="button"
                    class="wb-button wb-button--ghost"
                    @click="previousStep"
                >
                    Back
                </button>
                <span v-else />

                <button
                    v-if="activeStep < totalSteps"
                    type="button"
                    class="wb-button wb-button--primary"
                    @click="nextStep"
                >
                    Save and Continue
                </button>
                <button
                    v-else
                    type="button"
                    class="wb-button wb-button--primary"
                    :disabled="isCompleting || !canContinue"
                    @click="continueToScheduling"
                >
                    {{
                        isCompleting
                            ? "Redirecting…"
                            : canContinue
                              ? "Continue to Schedule Meeting"
                              : "Complete required sections first"
                    }}
                </button>
            </div>
        </section>
    </SurfaceShell>
</template>
