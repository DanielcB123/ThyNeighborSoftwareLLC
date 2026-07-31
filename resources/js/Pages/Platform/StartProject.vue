<script setup lang="ts">
import axios from "axios";
import { Head } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, reactive, ref, watch } from "vue";
import SurfaceShell from "@/layouts/shells/SurfaceShell.vue";

type StepKey = "project" | "business" | "goals" | "preparation" | "meeting";

interface PageCopy {
    readonly headTitle?: string;
    readonly title?: string;
    readonly summary?: string;
}

interface OnboardingSnapshot {
    sessionToken: string;
    workspacePublicId: string;
    resumeUrl: string;
    status: string;
    currentStep: number;
    lastSavedAt: string | null;
    responses: Record<string, Record<string, unknown>>;
}

interface ProjectDirectionOption {
    value: string;
    title: string;
    description: string;
}

interface OnboardingOptions {
    stepKeys: readonly StepKey[];
    projectDirections: readonly ProjectDirectionOption[];
    businessRoles: readonly string[];
    industries: readonly string[];
    goals: readonly string[];
    timelineComfort: readonly string[];
    budgetComfort: readonly string[];
    meetingFormats: readonly string[];
    assetChecklist: readonly string[];
}

interface UploadedMaterial {
    originalName: string;
    storagePath: string;
    mimeType: string;
    size: number;
    uploadedAt: string;
}

interface Stakeholder {
    name: string;
    email: string;
    role: string;
    inviteLater: boolean;
}

interface MeetingAttendee {
    name: string;
    email: string;
    role: string;
}

const props = defineProps<{
    onboarding: OnboardingSnapshot;
    options: OnboardingOptions;
    page?: PageCopy;
}>();

const stepLabels: readonly string[] = [
    "Project",
    "Business",
    "Goals",
    "Preparation",
    "Meeting",
];

const stepHints: readonly string[] = [
    "Required now",
    "Required now",
    "Required now",
    "Helpful before the meeting",
    "Required now",
];

const stepTimeHints: readonly string[] = [
    "About 8 minutes left",
    "About 6 minutes left",
    "About 4 minutes left",
    "About 2 minutes left",
    "About 1 minute left",
];

const pageTitle = computed(
    () => props.page?.title ?? "What are we helping you build?",
);
const pageSummary = computed(
    () =>
        props.page?.summary ??
        "Give us the short version. We use this to prepare for a focused discovery meeting.",
);

const session = ref<OnboardingSnapshot>({ ...props.onboarding });
const activeStep = ref(Math.min(Math.max(session.value.currentStep, 1), 5));
const saveState = ref<"saved" | "saving" | "unsaved" | "error">("saved");
const saveMessage = computed(() => {
    if (saveState.value === "saving") {
        return "Saving…";
    }
    if (saveState.value === "unsaved") {
        return "Unsaved changes";
    }
    if (saveState.value === "error") {
        return "Could not save";
    }
    return "Saved";
});

const validationErrors = ref<Record<string, string[]>>({});
const uploadError = ref<string | null>(null);
const isUploadingMaterials = ref(false);
const isSubmittingMeeting = ref(false);
const meetingPublicId = ref<string | null>(null);
let autoSaveTimer: ReturnType<typeof setTimeout> | null = null;

const responses = session.value.responses ?? {};

const form = reactive({
    project: {
        projectDirection:
            (responses.project?.projectDirection as string | undefined) ?? "",
        projectDirectionNotes:
            (responses.project?.projectDirectionNotes as string | undefined) ??
            "",
    },
    business: {
        contactName: (responses.business?.contactName as string | undefined) ?? "",
        businessName:
            (responses.business?.businessName as string | undefined) ?? "",
        businessEmail:
            (responses.business?.businessEmail as string | undefined) ?? "",
        phone: (responses.business?.phone as string | undefined) ?? "",
        industry: (responses.business?.industry as string | undefined) ?? "",
        industryOther:
            (responses.business?.industryOther as string | undefined) ?? "",
        businessLocation:
            (responses.business?.businessLocation as string | undefined) ?? "",
        locationCount:
            (responses.business?.locationCount as number | undefined) ?? 1,
        teamSize: (responses.business?.teamSize as string | undefined) ?? "",
        roleInBusiness:
            (responses.business?.roleInBusiness as string | undefined) ?? "",
        additionalStakeholders:
            (responses.business?.additionalStakeholders as Stakeholder[] | undefined) ??
            [],
    },
    goals: {
        goals: (responses.goals?.goals as string[] | undefined) ?? [],
        goalsOther: (responses.goals?.goalsOther as string | undefined) ?? "",
        currentProblems:
            (responses.goals?.currentProblems as string | undefined) ?? "",
        successLooksLike:
            (responses.goals?.successLooksLike as string | undefined) ?? "",
        knownConstraints:
            (responses.goals?.knownConstraints as string | undefined) ?? "",
        timelineComfort:
            (responses.goals?.timelineComfort as string | undefined) ?? "",
        budgetComfort:
            (responses.goals?.budgetComfort as string | undefined) ?? "",
    },
    preparation: {
        existingAssets:
            (responses.preparation?.existingAssets as string[] | undefined) ?? [],
        importantLinks:
            (responses.preparation?.importantLinks as string[] | undefined) ?? [""],
        notesForTeam:
            (responses.preparation?.notesForTeam as string | undefined) ?? "",
        uploadedMaterials:
            (responses.preparation?.uploadedMaterials as UploadedMaterial[] | undefined) ??
            [],
    },
    meeting: {
        meetingFormat:
            (responses.meeting?.meetingFormat as string | undefined) ?? "video",
        timezone:
            (responses.meeting?.timezone as string | undefined) ??
            Intl.DateTimeFormat().resolvedOptions().timeZone ??
            "UTC",
        preferredStartDate:
            (responses.meeting?.preferredStartDate as string | undefined) ?? "",
        preferredEndDate:
            (responses.meeting?.preferredEndDate as string | undefined) ?? "",
        availabilityNotes:
            (responses.meeting?.availabilityNotes as string | undefined) ?? "",
        attendees:
            (responses.meeting?.attendees as MeetingAttendee[] | undefined) ?? [],
    },
});

const resumeUrl = computed(() => session.value.resumeUrl);
const isMeetingRequested = computed(
    () => session.value.status === "meeting_requested" || meetingPublicId.value !== null,
);
const completionStep = computed(() => Math.max(activeStep.value, session.value.currentStep));

watch(
    () => activeStep.value,
    (newStep) => {
        if (newStep === 5 && form.meeting.attendees.length === 0) {
            const attendees: MeetingAttendee[] = [];

            if (form.business.contactName.trim() !== "") {
                attendees.push({
                    name: form.business.contactName.trim(),
                    email: form.business.businessEmail.trim(),
                    role: form.business.roleInBusiness.trim(),
                });
            }

            form.business.additionalStakeholders.forEach((stakeholder) => {
                if (stakeholder.name.trim() === "") {
                    return;
                }

                attendees.push({
                    name: stakeholder.name.trim(),
                    email: stakeholder.email.trim(),
                    role: stakeholder.role.trim(),
                });
            });

            if (attendees.length === 0) {
                attendees.push({
                    name: "",
                    email: "",
                    role: "",
                });
            }

            form.meeting.attendees = attendees;
        }
    },
);

function stepKeyFor(stepNumber: number): StepKey {
    return props.options.stepKeys[stepNumber - 1] ?? "project";
}

function setSessionSnapshot(snapshot: OnboardingSnapshot): void {
    session.value = { ...snapshot };
}

function queueAutosave(): void {
    if (isMeetingRequested.value) {
        return;
    }

    saveState.value = "unsaved";

    if (autoSaveTimer !== null) {
        clearTimeout(autoSaveTimer);
    }

    autoSaveTimer = setTimeout(() => {
        void saveCurrentStep(false);
    }, 900);
}

function errorMessages(field: string): string[] {
    return validationErrors.value[`payload.${field}`] ?? [];
}

function clearFieldError(field: string): void {
    delete validationErrors.value[`payload.${field}`];
}

function payloadForStep(stepKey: StepKey): Record<string, unknown> {
    if (stepKey === "project") {
        return {
            projectDirection: form.project.projectDirection,
            projectDirectionNotes: form.project.projectDirectionNotes,
        };
    }

    if (stepKey === "business") {
        return {
            contactName: form.business.contactName,
            businessName: form.business.businessName,
            businessEmail: form.business.businessEmail,
            phone: form.business.phone,
            industry: form.business.industry,
            industryOther: form.business.industryOther,
            businessLocation: form.business.businessLocation,
            locationCount: form.business.locationCount,
            teamSize: form.business.teamSize,
            roleInBusiness: form.business.roleInBusiness,
            additionalStakeholders: form.business.additionalStakeholders,
        };
    }

    if (stepKey === "goals") {
        return {
            goals: form.goals.goals,
            goalsOther: form.goals.goalsOther,
            currentProblems: form.goals.currentProblems,
            successLooksLike: form.goals.successLooksLike,
            knownConstraints: form.goals.knownConstraints,
            timelineComfort: form.goals.timelineComfort,
            budgetComfort: form.goals.budgetComfort,
        };
    }

    if (stepKey === "preparation") {
        return {
            existingAssets: form.preparation.existingAssets,
            importantLinks: form.preparation.importantLinks.filter(
                (link) => link.trim() !== "",
            ),
            notesForTeam: form.preparation.notesForTeam,
            uploadedMaterials: form.preparation.uploadedMaterials,
        };
    }

    return {
        meetingFormat: form.meeting.meetingFormat,
        timezone: form.meeting.timezone,
        preferredStartDate: form.meeting.preferredStartDate,
        preferredEndDate: form.meeting.preferredEndDate,
        availabilityNotes: form.meeting.availabilityNotes,
        attendees: form.meeting.attendees,
    };
}

async function saveCurrentStep(userTriggered: boolean): Promise<boolean> {
    const stepKey = stepKeyFor(activeStep.value);

    saveState.value = "saving";

    try {
        const response = await axios.post("/start-project/session", {
            sessionToken: session.value.sessionToken,
            stepKey,
            payload: payloadForStep(stepKey),
        });

        validationErrors.value = {};
        setSessionSnapshot(response.data.session as OnboardingSnapshot);
        saveState.value = "saved";
        return true;
    } catch (error: unknown) {
        saveState.value = "error";

        const maybeResponse = (error as { response?: { status?: number; data?: { errors?: Record<string, string[]> } } })
            .response;

        if (maybeResponse?.status === 422 && maybeResponse.data?.errors) {
            validationErrors.value = maybeResponse.data.errors;
        }

        if (userTriggered) {
            return false;
        }
    }

    return false;
}

async function nextStep(): Promise<void> {
    const saved = await saveCurrentStep(true);
    if (!saved) {
        return;
    }

    if (activeStep.value < 5) {
        activeStep.value += 1;
    }
}

function previousStep(): void {
    if (activeStep.value > 1) {
        activeStep.value -= 1;
    }
}

function toggleGoal(goal: string): void {
    const hasGoal = form.goals.goals.includes(goal);
    form.goals.goals = hasGoal
        ? form.goals.goals.filter((existingGoal) => existingGoal !== goal)
        : [...form.goals.goals, goal];
    queueAutosave();
}

function toggleAsset(asset: string): void {
    const hasAsset = form.preparation.existingAssets.includes(asset);
    form.preparation.existingAssets = hasAsset
        ? form.preparation.existingAssets.filter((existingAsset) => existingAsset !== asset)
        : [...form.preparation.existingAssets, asset];
    queueAutosave();
}

function addStakeholder(): void {
    form.business.additionalStakeholders.push({
        name: "",
        email: "",
        role: "",
        inviteLater: true,
    });
}

function removeStakeholder(index: number): void {
    form.business.additionalStakeholders.splice(index, 1);
    queueAutosave();
}

function addMeetingAttendee(): void {
    form.meeting.attendees.push({
        name: "",
        email: "",
        role: "",
    });
}

function removeMeetingAttendee(index: number): void {
    form.meeting.attendees.splice(index, 1);
    if (form.meeting.attendees.length === 0) {
        addMeetingAttendee();
    }
    queueAutosave();
}

function addImportantLink(): void {
    form.preparation.importantLinks.push("");
}

function removeImportantLink(index: number): void {
    form.preparation.importantLinks.splice(index, 1);
    if (form.preparation.importantLinks.length === 0) {
        form.preparation.importantLinks.push("");
    }
    queueAutosave();
}

async function uploadMaterials(event: Event): Promise<void> {
    const input = event.target as HTMLInputElement;
    const selectedFiles = input.files;

    if (!selectedFiles || selectedFiles.length === 0) {
        return;
    }

    uploadError.value = null;
    isUploadingMaterials.value = true;

    const formData = new FormData();
    formData.append("sessionToken", session.value.sessionToken);
    Array.from(selectedFiles).forEach((file) => formData.append("materials[]", file));

    try {
        const response = await axios.post(
            "/start-project/session/materials",
            formData,
            {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            },
        );

        const updatedSession = response.data.session as OnboardingSnapshot;
        setSessionSnapshot(updatedSession);
        form.preparation.uploadedMaterials =
            (updatedSession.responses.preparation?.uploadedMaterials as UploadedMaterial[] | undefined) ??
            form.preparation.uploadedMaterials;
        saveState.value = "saved";
    } catch (_error) {
        uploadError.value =
            "Upload failed. Please verify file type/size and try again.";
    } finally {
        isUploadingMaterials.value = false;
        input.value = "";
    }
}

async function requestDiscoveryMeeting(): Promise<void> {
    isSubmittingMeeting.value = true;

    try {
        const response = await axios.post("/start-project/session/schedule", {
            sessionToken: session.value.sessionToken,
            payload: payloadForStep("meeting"),
        });

        meetingPublicId.value = response.data.meetingPublicId as string;
        setSessionSnapshot(response.data.session as OnboardingSnapshot);
        saveState.value = "saved";
    } catch (error: unknown) {
        const maybeResponse = (error as { response?: { status?: number; data?: { errors?: Record<string, string[]> } } })
            .response;

        if (maybeResponse?.status === 422 && maybeResponse.data?.errors) {
            validationErrors.value = maybeResponse.data.errors;
        }

        saveState.value = "error";
    } finally {
        isSubmittingMeeting.value = false;
    }
}

onBeforeUnmount(() => {
    if (autoSaveTimer !== null) {
        clearTimeout(autoSaveTimer);
    }
});
</script>

<template>
    <Head :title="props.page?.headTitle ?? pageTitle" />
    <SurfaceShell :page-title="pageTitle" :page-summary="pageSummary">
        <section class="wb-section-frame grid gap-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="grid gap-1">
                    <p class="wb-shell-kicker">Step {{ completionStep }} of 5</p>
                    <p class="wb-body-s text-[var(--color-text-muted)]">
                        {{ stepTimeHints[Math.max(activeStep - 1, 0)] }} · {{ stepHints[Math.max(activeStep - 1, 0)] }}
                    </p>
                </div>
                <p class="wb-pill">{{ saveMessage }}</p>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div class="wb-card p-4">
                    <p class="wb-micro-label text-[var(--color-text-muted)]">Workspace</p>
                    <p class="wb-body-m mt-2 wb-code">{{ session.workspacePublicId }}</p>
                    <p class="wb-body-s mt-3 text-[var(--color-text-muted)]">
                        Your intake is saved continuously. You can reopen this link anytime.
                    </p>
                </div>
                <div class="wb-card p-4">
                    <label
                        for="resume-link"
                        class="wb-micro-label text-[var(--color-text-muted)]"
                    >
                        Resume Link
                    </label>
                    <input
                        id="resume-link"
                        :value="resumeUrl"
                        readonly
                        class="mt-2 w-full rounded-md border border-[var(--color-border)] bg-[var(--color-surface)] px-3 py-2 text-sm"
                    />
                </div>
            </div>

            <ol class="grid gap-2 md:grid-cols-5">
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

        <section
            v-if="isMeetingRequested"
            class="wb-section-frame wb-cta-band mt-8 grid gap-5"
        >
            <p class="wb-shell-kicker">Discovery meeting requested</p>
            <h2 class="wb-heading-2">
                Thank you. Your onboarding workspace is ready.
            </h2>
            <p class="wb-body-m text-[var(--color-text-muted)]">
                We received your preparation details and meeting request. WeBuildYouThrive
                will follow up with confirmation options and next steps.
            </p>
            <p class="wb-body-s">
                Meeting request reference:
                <span class="wb-code">{{ meetingPublicId ?? "Pending assignment" }}</span>
            </p>
        </section>

        <section
            v-else
            class="mt-8 grid gap-6"
            @input="queueAutosave"
            @change="queueAutosave"
        >
            <div v-if="activeStep === 1" class="wb-section-frame grid gap-6">
                <div>
                    <p class="wb-shell-kicker">Project Direction</p>
                    <h2 class="wb-heading-2 mt-2">What are we helping you build?</h2>
                    <p class="wb-body-s mt-2 text-[var(--color-text-muted)]">
                        Choose the closest fit. This only helps us shape the meeting.
                    </p>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <button
                        v-for="direction in options.projectDirections"
                        :key="direction.value"
                        type="button"
                        class="wb-card p-4 text-left transition"
                        :class="{
                            'border-[color:var(--color-accent)]': form.project.projectDirection === direction.value,
                        }"
                        @click="
                            form.project.projectDirection = direction.value;
                            clearFieldError('projectDirection');
                            queueAutosave();
                        "
                    >
                        <p class="wb-heading-3">{{ direction.title }}</p>
                        <p class="wb-body-s mt-2 text-[var(--color-text-muted)]">
                            {{ direction.description }}
                        </p>
                    </button>
                </div>

                <label class="grid gap-2">
                    <span class="wb-body-s">Helpful context (optional)</span>
                    <textarea
                        v-model="form.project.projectDirectionNotes"
                        rows="4"
                        class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        placeholder="Share a quick summary of what prompted this project."
                    />
                </label>

                <p
                    v-for="error in errorMessages('projectDirection')"
                    :key="error"
                    class="text-sm text-[var(--color-danger)]"
                >
                    {{ error }}
                </p>
            </div>

            <div v-if="activeStep === 2" class="wb-section-frame grid gap-6">
                <div>
                    <p class="wb-shell-kicker">Business Context</p>
                    <h2 class="wb-heading-2 mt-2">Tell us who we are planning with</h2>
                    <p class="wb-body-s mt-2 text-[var(--color-text-muted)]">
                        Required now: business and contact basics for meeting prep.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2">
                        <span class="wb-body-s">Contact name *</span>
                        <input
                            v-model="form.business.contactName"
                            type="text"
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        />
                    </label>
                    <label class="grid gap-2">
                        <span class="wb-body-s">Business name *</span>
                        <input
                            v-model="form.business.businessName"
                            type="text"
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        />
                    </label>
                    <label class="grid gap-2">
                        <span class="wb-body-s">Business email *</span>
                        <input
                            v-model="form.business.businessEmail"
                            type="email"
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        />
                    </label>
                    <label class="grid gap-2">
                        <span class="wb-body-s">Phone number *</span>
                        <input
                            v-model="form.business.phone"
                            type="tel"
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        />
                    </label>
                    <label class="grid gap-2">
                        <span class="wb-body-s">Industry *</span>
                        <select
                            v-model="form.business.industry"
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        >
                            <option value="">Select industry</option>
                            <option
                                v-for="industry in options.industries"
                                :key="industry"
                                :value="industry"
                            >
                                {{ industry }}
                            </option>
                        </select>
                    </label>
                    <label
                        v-if="form.business.industry === 'Other'"
                        class="grid gap-2"
                    >
                        <span class="wb-body-s">Industry clarification</span>
                        <input
                            v-model="form.business.industryOther"
                            type="text"
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        />
                    </label>
                    <label class="grid gap-2">
                        <span class="wb-body-s">Business location *</span>
                        <input
                            v-model="form.business.businessLocation"
                            type="text"
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        />
                    </label>
                    <label class="grid gap-2">
                        <span class="wb-body-s">Number of locations *</span>
                        <input
                            v-model.number="form.business.locationCount"
                            type="number"
                            min="1"
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        />
                    </label>
                    <label class="grid gap-2">
                        <span class="wb-body-s">Approximate team size *</span>
                        <input
                            v-model="form.business.teamSize"
                            type="text"
                            placeholder="Example: 11-25"
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        />
                    </label>
                    <label class="grid gap-2">
                        <span class="wb-body-s">Your role in the business *</span>
                        <select
                            v-model="form.business.roleInBusiness"
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        >
                            <option value="">Select role</option>
                            <option
                                v-for="role in options.businessRoles"
                                :key="role"
                                :value="role"
                            >
                                {{ role }}
                            </option>
                        </select>
                    </label>
                </div>

                <div class="grid gap-3">
                    <div class="flex items-center justify-between">
                        <h3 class="wb-heading-3">
                            Who else should be involved? (optional)
                        </h3>
                        <button
                            type="button"
                            class="wb-button wb-button--ghost"
                            @click="addStakeholder"
                        >
                            Add stakeholder
                        </button>
                    </div>
                    <div
                        v-for="(stakeholder, index) in form.business.additionalStakeholders"
                        :key="`stakeholder-${index}`"
                        class="wb-card grid gap-3 p-4 md:grid-cols-2"
                    >
                        <label class="grid gap-2">
                            <span class="wb-body-s">Name</span>
                            <input
                                v-model="stakeholder.name"
                                type="text"
                                class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                            />
                        </label>
                        <label class="grid gap-2">
                            <span class="wb-body-s">Email</span>
                            <input
                                v-model="stakeholder.email"
                                type="email"
                                class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                            />
                        </label>
                        <label class="grid gap-2">
                            <span class="wb-body-s">Role</span>
                            <input
                                v-model="stakeholder.role"
                                type="text"
                                class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                            />
                        </label>
                        <label class="flex items-center gap-2">
                            <input
                                v-model="stakeholder.inviteLater"
                                type="checkbox"
                            />
                            <span class="wb-body-s">
                                Invite later after primary confirmation
                            </span>
                        </label>
                        <button
                            type="button"
                            class="wb-button wb-button--ghost md:col-span-2"
                            @click="removeStakeholder(index)"
                        >
                            Remove stakeholder
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="activeStep === 3" class="wb-section-frame grid gap-6">
                <div>
                    <p class="wb-shell-kicker">Goals and Current Problem</p>
                    <h2 class="wb-heading-2 mt-2">
                        What should this project accomplish?
                    </h2>
                    <p class="wb-body-s mt-2 text-[var(--color-text-muted)]">
                        The form prepares the meeting. You do not need to design full technical specifications.
                    </p>
                </div>

                <div class="grid gap-2">
                    <p class="wb-body-s">Select goals *</p>
                    <div class="grid gap-2 md:grid-cols-2">
                        <button
                            v-for="goal in options.goals"
                            :key="goal"
                            type="button"
                            class="wb-card px-4 py-3 text-left"
                            :class="{
                                'border-[color:var(--color-accent)]': form.goals.goals.includes(goal),
                            }"
                            @click="toggleGoal(goal)"
                        >
                            <span class="wb-body-s">{{ goal }}</span>
                        </button>
                    </div>
                </div>

                <label class="grid gap-2">
                    <span class="wb-body-s">Anything else we should know about goals?</span>
                    <textarea
                        v-model="form.goals.goalsOther"
                        rows="3"
                        class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                    />
                </label>

                <label class="grid gap-2">
                    <span class="wb-body-s">What is not working well today? *</span>
                    <textarea
                        v-model="form.goals.currentProblems"
                        rows="4"
                        class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        placeholder="What is slowing your team down or limiting customer experience?"
                    />
                </label>

                <label class="grid gap-2">
                    <span class="wb-body-s">What would make this project feel successful? *</span>
                    <textarea
                        v-model="form.goals.successLooksLike"
                        rows="4"
                        class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                    />
                </label>

                <label class="grid gap-2">
                    <span class="wb-body-s">Known constraints (optional)</span>
                    <textarea
                        v-model="form.goals.knownConstraints"
                        rows="3"
                        class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        placeholder="Team bandwidth, compliance boundaries, integration limitations, etc."
                    />
                </label>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2">
                        <span class="wb-body-s">Timeline comfort *</span>
                        <select
                            v-model="form.goals.timelineComfort"
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        >
                            <option value="">Select timeline</option>
                            <option
                                v-for="option in options.timelineComfort"
                                :key="option"
                                :value="option"
                            >
                                {{ option }}
                            </option>
                        </select>
                    </label>

                    <label class="grid gap-2">
                        <span class="wb-body-s">Budget comfort *</span>
                        <select
                            v-model="form.goals.budgetComfort"
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        >
                            <option value="">Select budget comfort</option>
                            <option
                                v-for="option in options.budgetComfort"
                                :key="option"
                                :value="option"
                            >
                                {{ option }}
                            </option>
                        </select>
                    </label>
                </div>
            </div>

            <div v-if="activeStep === 4" class="wb-section-frame grid gap-6">
                <div>
                    <p class="wb-shell-kicker">Useful Preparation Details</p>
                    <h2 class="wb-heading-2 mt-2">
                        Share what helps us prepare. Keep it lightweight.
                    </h2>
                    <p class="wb-body-s mt-2 text-[var(--color-text-muted)]">
                        Helpful before the meeting. If you do not have something yet, discuss it later.
                    </p>
                </div>

                <div class="grid gap-2">
                    <p class="wb-body-s">Which assets already exist?</p>
                    <div class="grid gap-2 md:grid-cols-2">
                        <button
                            v-for="asset in options.assetChecklist"
                            :key="asset"
                            type="button"
                            class="wb-card px-4 py-3 text-left"
                            :class="{
                                'border-[color:var(--color-accent)]': form.preparation.existingAssets.includes(asset),
                            }"
                            @click="toggleAsset(asset)"
                        >
                            <span class="wb-body-s">{{ asset }}</span>
                        </button>
                    </div>
                </div>

                <div class="grid gap-3">
                    <div class="flex items-center justify-between">
                        <p class="wb-body-s">Useful links (optional)</p>
                        <button
                            type="button"
                            class="wb-button wb-button--ghost"
                            @click="addImportantLink"
                        >
                            Add link
                        </button>
                    </div>
                    <div
                        v-for="(link, index) in form.preparation.importantLinks"
                        :key="`important-link-${index}`"
                        class="flex items-center gap-2"
                    >
                        <input
                            v-model="form.preparation.importantLinks[index]"
                            type="url"
                            placeholder="https://"
                            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        />
                        <button
                            type="button"
                            class="wb-button wb-button--ghost"
                            @click="removeImportantLink(index)"
                        >
                            Remove
                        </button>
                    </div>
                </div>

                <label class="grid gap-2">
                    <span class="wb-body-s">Anything else we should review before the meeting?</span>
                    <textarea
                        v-model="form.preparation.notesForTeam"
                        rows="4"
                        class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                    />
                </label>

                <div class="grid gap-3">
                    <label class="wb-body-s" for="material-upload">
                        Upload useful materials (optional)
                    </label>
                    <input
                        id="material-upload"
                        type="file"
                        multiple
                        class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        @change="uploadMaterials"
                    />
                    <p class="wb-body-s text-[var(--color-text-muted)]">
                        Accepted: PDF, Office docs, CSV, images, ZIP (up to 20MB per file).
                    </p>
                    <p
                        v-if="isUploadingMaterials"
                        class="wb-body-s text-[var(--color-text-muted)]"
                    >
                        Uploading…
                    </p>
                    <p
                        v-if="uploadError"
                        class="wb-body-s text-[var(--color-danger)]"
                    >
                        {{ uploadError }}
                    </p>
                    <ul class="grid gap-2">
                        <li
                            v-for="material in form.preparation.uploadedMaterials"
                            :key="material.storagePath"
                            class="wb-card p-3"
                        >
                            <p class="wb-body-s">{{ material.originalName }}</p>
                            <p class="wb-micro-label text-[var(--color-text-muted)]">
                                {{ material.mimeType }} · {{ Math.round(material.size / 1024) }} KB
                            </p>
                        </li>
                    </ul>
                </div>
            </div>

            <div v-if="activeStep === 5" class="wb-section-frame grid gap-6">
                <div>
                    <p class="wb-shell-kicker">Meeting Scheduling</p>
                    <h2 class="wb-heading-2 mt-2">
                        Let’s schedule your discovery conversation.
                    </h2>
                    <p class="wb-body-s mt-2 text-[var(--color-text-muted)]">
                        Required now: availability + attendees. We handle full specification details together in the meeting.
                    </p>
                </div>

                <div class="grid gap-2">
                    <p class="wb-body-s">Preferred meeting format *</p>
                    <div class="grid gap-2 md:grid-cols-3">
                        <button
                            v-for="format in options.meetingFormats"
                            :key="format"
                            type="button"
                            class="wb-card px-4 py-3 text-left capitalize"
                            :class="{
                                'border-[color:var(--color-accent)]': form.meeting.meetingFormat === format,
                            }"
                            @click="form.meeting.meetingFormat = format"
                        >
                            {{ format }}
                        </button>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <label class="grid gap-2">
                        <span class="wb-body-s">Timezone *</span>
                        <input
                            v-model="form.meeting.timezone"
                            type="text"
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        />
                    </label>
                    <label class="grid gap-2">
                        <span class="wb-body-s">Preferred start date *</span>
                        <input
                            v-model="form.meeting.preferredStartDate"
                            type="date"
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        />
                    </label>
                    <label class="grid gap-2">
                        <span class="wb-body-s">Preferred end date *</span>
                        <input
                            v-model="form.meeting.preferredEndDate"
                            type="date"
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        />
                    </label>
                </div>

                <label class="grid gap-2">
                    <span class="wb-body-s">Availability notes *</span>
                    <textarea
                        v-model="form.meeting.availabilityNotes"
                        rows="4"
                        class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        placeholder="Days/times that work best, known blackouts, and preferred sequence."
                    />
                </label>

                <div class="grid gap-3">
                    <div class="flex items-center justify-between">
                        <h3 class="wb-heading-3">Meeting attendees *</h3>
                        <button
                            type="button"
                            class="wb-button wb-button--ghost"
                            @click="addMeetingAttendee"
                        >
                            Add attendee
                        </button>
                    </div>
                    <div
                        v-for="(attendee, index) in form.meeting.attendees"
                        :key="`attendee-${index}`"
                        class="wb-card grid gap-3 p-4 md:grid-cols-3"
                    >
                        <label class="grid gap-2">
                            <span class="wb-body-s">Name</span>
                            <input
                                v-model="attendee.name"
                                type="text"
                                class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                            />
                        </label>
                        <label class="grid gap-2">
                            <span class="wb-body-s">Email</span>
                            <input
                                v-model="attendee.email"
                                type="email"
                                class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                            />
                        </label>
                        <label class="grid gap-2">
                            <span class="wb-body-s">Role</span>
                            <input
                                v-model="attendee.role"
                                type="text"
                                class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                            />
                        </label>
                        <button
                            type="button"
                            class="wb-button wb-button--ghost md:col-span-3"
                            @click="removeMeetingAttendee(index)"
                        >
                            Remove attendee
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="Object.keys(validationErrors).length > 0" class="wb-card p-4">
                <p class="wb-body-s text-[var(--color-danger)]">
                    Please review highlighted requirements before continuing.
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
                    v-if="activeStep < 5"
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
                    :disabled="isSubmittingMeeting"
                    @click="requestDiscoveryMeeting"
                >
                    {{ isSubmittingMeeting ? "Submitting…" : "Request Discovery Meeting" }}
                </button>
            </div>
        </section>
    </SurfaceShell>
</template>
