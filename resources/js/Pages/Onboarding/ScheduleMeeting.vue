<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import SurfaceShell from "@/layouts/shells/SurfaceShell.vue";

interface Appointment {
    publicId: string;
    businessName: string;
    contactName: string;
    contactEmail: string;
    status: string;
    scheduledAt: string | null;
    timezone: string;
    durationMinutes: number;
    zoomJoinUrl: string | null;
    zoomPasscode: string | null;
}

interface IntakeSummary {
    business?: Record<string, string>;
    project?: Record<string, unknown>;
    overview?: Record<string, string>;
    features?: Record<string, unknown>;
    assets?: Record<string, unknown>;
    timeline?: Record<string, string>;
    additional?: Record<string, string>;
}

const props = defineProps<{
    appointment: Appointment;
    token: string;
    timezoneOptions: string[];
    status?: string | null;
    intakeSummary?: IntakeSummary | null;
}>();

const pageTitle = "Schedule Your Discovery Call";
const pageSummary =
    "Confirm your preferred time so we can review your intake and arrive prepared with a focused plan.";
const TIME_SLOT_START_HOUR = 8;
const TIME_SLOT_END_HOUR = 18;
const TIME_SLOT_INTERVAL_MINUTES = 30;

const formatDateForInput = (isoString: string | null, timezone: string): string => {
    if (!isoString) {
        return "";
    }

    const date = new Date(isoString);
    return new Intl.DateTimeFormat("en-CA", {
        timeZone: timezone,
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    }).format(date);
};

const formatTimeForInput = (isoString: string | null, timezone: string): string => {
    if (!isoString) {
        return "";
    }

    const date = new Date(isoString);
    return new Intl.DateTimeFormat("en-GB", {
        timeZone: timezone,
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
    }).format(date);
};

const showingRescheduleForm = ref(props.appointment.status !== "scheduled");

const form = useForm({
    token: props.token,
    business_name: props.appointment.businessName || "",
    contact_name: props.appointment.contactName || "",
    contact_email: props.appointment.contactEmail || "",
    meeting_date: formatDateForInput(props.appointment.scheduledAt, props.appointment.timezone),
    meeting_time: formatTimeForInput(props.appointment.scheduledAt, props.appointment.timezone),
    timezone:
        props.appointment.timezone ||
        Intl.DateTimeFormat().resolvedOptions().timeZone ||
        "UTC",
    duration_minutes: props.appointment.durationMinutes || 45,
});

const scheduledLabel = computed(() => {
    if (!props.appointment.scheduledAt) {
        return null;
    }

    const date = new Date(props.appointment.scheduledAt);
    const dateText = new Intl.DateTimeFormat("en-US", {
        timeZone: props.appointment.timezone,
        weekday: "long",
        month: "long",
        day: "numeric",
        year: "numeric",
    }).format(date);
    const timeText = new Intl.DateTimeFormat("en-US", {
        timeZone: props.appointment.timezone,
        hour: "numeric",
        minute: "2-digit",
    }).format(date);

    return `${dateText} at ${timeText} (${props.appointment.timezone})`;
});

const selectedProjectTypes = computed<string[]>(() => {
    const value = props.intakeSummary?.project?.projectTypes;
    return Array.isArray(value) ? value.map((item) => String(item)) : [];
});

const selectedFeatures = computed<string[]>(() => {
    const value = props.intakeSummary?.features?.requestedFeatures;
    return Array.isArray(value) ? value.map((item) => String(item)) : [];
});

const selectedAssets = computed<string[]>(() => {
    const value = props.intakeSummary?.assets?.existingAssets;
    return Array.isArray(value) ? value.map((item) => String(item)) : [];
});

const timeSlotLabel = (slot: string): string => {
    const [hourText, minuteText] = slot.split(":");
    const hour24 = Number.parseInt(hourText ?? "", 10);
    const minute = Number.parseInt(minuteText ?? "", 10);

    if (Number.isNaN(hour24) || Number.isNaN(minute)) {
        return slot;
    }

    const meridiem = hour24 >= 12 ? "PM" : "AM";
    const hour12 = hour24 % 12 === 0 ? 12 : hour24 % 12;
    return `${hour12}:${String(minute).padStart(2, "0")} ${meridiem}`;
};

const timeSlots = computed<string[]>(() => {
    const slots: string[] = [];

    for (let hour = TIME_SLOT_START_HOUR; hour <= TIME_SLOT_END_HOUR; hour += 1) {
        for (let minute = 0; minute < 60; minute += TIME_SLOT_INTERVAL_MINUTES) {
            if (hour === TIME_SLOT_END_HOUR && minute > 0) {
                continue;
            }

            slots.push(`${String(hour).padStart(2, "0")}:${String(minute).padStart(2, "0")}`);
        }
    }

    if (
        /^([01]\d|2[0-3]):[0-5]\d$/.test(form.meeting_time) &&
        !slots.includes(form.meeting_time)
    ) {
        slots.push(form.meeting_time);
        slots.sort();
    }

    return slots;
});

const submit = (): void => {
    if (!/^([01]\d|2[0-3]):[0-5]\d$/.test(form.meeting_time)) {
        form.setError("meeting_time", "Please select a valid meeting time.");
        return;
    }

    form.clearErrors("meeting_time");

    form.put(route("onboarding.schedule", props.appointment.publicId), {
        preserveScroll: true,
        onSuccess: () => {
            showingRescheduleForm.value = false;
        },
    });
};

const cancelMeeting = (): void => {
    if (!window.confirm("Cancel this onboarding meeting?")) {
        return;
    }

    form.delete(route("onboarding.cancel", props.appointment.publicId), {
        preserveScroll: true,
        data: { token: form.token },
    });
};
</script>

<template>
    <Head title="Schedule Discovery Call" />

    <SurfaceShell :page-title="pageTitle" :page-summary="pageSummary">
        <section class="wb-section-frame grid gap-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="wb-shell-kicker">Onboarding meeting</p>
                <p class="wb-pill">Status: {{ appointment.status }}</p>
            </div>
            <div v-if="status === 'scheduled'" class="wb-card p-4">
                <p class="wb-body-s text-[var(--color-success)]">
                    Your discovery call is scheduled.
                </p>
            </div>
            <div v-if="status === 'cancelled'" class="wb-card p-4">
                <p class="wb-body-s text-[var(--color-warning)]">
                    Your discovery call has been cancelled.
                </p>
            </div>
        </section>

        <section v-if="intakeSummary" class="mt-8 wb-section-frame grid gap-4">
            <h2 class="wb-heading-2">Client Discovery Briefing</h2>
            <p class="wb-body-s text-[var(--color-text-muted)]">
                Submitted intake responses for meeting preparation.
            </p>

            <div class="grid gap-3 md:grid-cols-2">
                <div class="wb-card p-4">
                    <p class="wb-micro-label text-[var(--color-text-muted)]">Business</p>
                    <p class="wb-body-m mt-2">
                        {{ intakeSummary.business?.businessName || "—" }}
                    </p>
                    <p class="wb-body-s text-[var(--color-text-muted)]">
                        {{ intakeSummary.business?.contactName || "—" }} ·
                        {{ intakeSummary.business?.businessEmail || "—" }}
                    </p>
                </div>

                <div class="wb-card p-4">
                    <p class="wb-micro-label text-[var(--color-text-muted)]">Project Type</p>
                    <p class="wb-body-s mt-2">
                        {{ selectedProjectTypes.length > 0 ? selectedProjectTypes.join(", ") : "—" }}
                    </p>
                </div>

                <div class="wb-card p-4 md:col-span-2">
                    <p class="wb-micro-label text-[var(--color-text-muted)]">Goals / Overview</p>
                    <p class="wb-body-s mt-2 whitespace-pre-line">
                        {{
                            intakeSummary.overview?.businessGoals ||
                            intakeSummary.overview?.whatToBuild ||
                            "—"
                        }}
                    </p>
                </div>

                <div class="wb-card p-4">
                    <p class="wb-micro-label text-[var(--color-text-muted)]">Requested Features</p>
                    <p class="wb-body-s mt-2">
                        {{ selectedFeatures.length > 0 ? selectedFeatures.join(", ") : "—" }}
                    </p>
                </div>

                <div class="wb-card p-4">
                    <p class="wb-micro-label text-[var(--color-text-muted)]">Existing Assets</p>
                    <p class="wb-body-s mt-2">
                        {{ selectedAssets.length > 0 ? selectedAssets.join(", ") : "—" }}
                    </p>
                </div>
            </div>
        </section>

        <section class="mt-8">
            <div
                v-if="appointment.status === 'scheduled' && !showingRescheduleForm"
                class="wb-section-frame grid gap-4"
            >
                <h2 class="wb-heading-2">Your Discovery Call Is Scheduled</h2>
                <p class="wb-body-m">{{ scheduledLabel }}</p>

                <a
                    v-if="appointment.zoomJoinUrl"
                    :href="appointment.zoomJoinUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="wb-button wb-button--primary w-fit"
                >
                    Join Zoom Meeting
                </a>

                <p v-if="appointment.zoomPasscode" class="wb-body-s text-[var(--color-text-muted)]">
                    Passcode: {{ appointment.zoomPasscode }}
                </p>

                <div class="flex flex-wrap gap-3">
                    <button type="button" class="wb-button wb-button--ghost" @click="showingRescheduleForm = true">
                        Reschedule
                    </button>
                    <button type="button" class="wb-button wb-button--ghost" @click="cancelMeeting">
                        Cancel Meeting
                    </button>
                </div>
            </div>

            <div v-else class="wb-section-frame">
                <form class="grid gap-4" @submit.prevent="submit">
                    <input v-model="form.token" type="hidden" />

                    <label class="grid gap-2">
                        <span class="wb-body-s">Business Name *</span>
                        <input
                            v-model="form.business_name"
                            type="text"
                            required
                            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                        />
                        <p v-if="form.errors.business_name" class="wb-body-s text-[var(--color-danger)]">
                            {{ form.errors.business_name }}
                        </p>
                    </label>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="grid gap-2">
                            <span class="wb-body-s">Your Name *</span>
                            <input
                                v-model="form.contact_name"
                                type="text"
                                required
                                class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                            />
                            <p v-if="form.errors.contact_name" class="wb-body-s text-[var(--color-danger)]">
                                {{ form.errors.contact_name }}
                            </p>
                        </label>

                        <label class="grid gap-2">
                            <span class="wb-body-s">Email *</span>
                            <input
                                v-model="form.contact_email"
                                type="email"
                                required
                                class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                            />
                            <p v-if="form.errors.contact_email" class="wb-body-s text-[var(--color-danger)]">
                                {{ form.errors.contact_email }}
                            </p>
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="grid gap-2">
                            <span class="wb-body-s">Meeting Date *</span>
                            <input
                                v-model="form.meeting_date"
                                type="date"
                                required
                                class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                            />
                            <p v-if="form.errors.meeting_date" class="wb-body-s text-[var(--color-danger)]">
                                {{ form.errors.meeting_date }}
                            </p>
                        </label>

                        <label class="grid gap-2">
                            <span class="wb-body-s">Meeting Time *</span>
                            <select
                                v-model="form.meeting_time"
                                required
                                class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                            >
                                <option value="">Select a meeting time</option>
                                <option
                                    v-for="slot in timeSlots"
                                    :key="slot"
                                    :value="slot"
                                >
                                    {{ timeSlotLabel(slot) }}
                                </option>
                            </select>
                            <p class="wb-body-s text-[var(--color-text-muted)]">
                                Choose an available start time.
                            </p>
                            <p v-if="form.errors.meeting_time" class="wb-body-s text-[var(--color-danger)]">
                                {{ form.errors.meeting_time }}
                            </p>
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="grid gap-2">
                            <span class="wb-body-s">Timezone *</span>
                            <select
                                v-model="form.timezone"
                                class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                            >
                                <option
                                    v-for="timezone in timezoneOptions"
                                    :key="timezone"
                                    :value="timezone"
                                >
                                    {{ timezone }}
                                </option>
                            </select>
                            <p v-if="form.errors.timezone" class="wb-body-s text-[var(--color-danger)]">
                                {{ form.errors.timezone }}
                            </p>
                        </label>

                        <label class="grid gap-2">
                            <span class="wb-body-s">Duration (minutes) *</span>
                            <select
                                v-model="form.duration_minutes"
                                required
                                class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
                            >
                                <option :value="30">30 minutes</option>
                                <option :value="45">45 minutes</option>
                                <option :value="60">60 minutes</option>
                                <option :value="90">90 minutes</option>
                            </select>
                            <p v-if="form.errors.duration_minutes" class="wb-body-s text-[var(--color-danger)]">
                                {{ form.errors.duration_minutes }}
                            </p>
                        </label>
                    </div>

                    <p v-if="form.errors.meeting" class="wb-body-s text-[var(--color-danger)]">
                        {{ form.errors.meeting }}
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="wb-button wb-button--primary" :disabled="form.processing">
                            {{ appointment.status === "scheduled" ? "Update Meeting" : "Schedule Meeting" }}
                        </button>
                        <button
                            v-if="appointment.status === 'scheduled'"
                            type="button"
                            class="wb-button wb-button--ghost"
                            @click="showingRescheduleForm = false"
                        >
                            Back
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </SurfaceShell>
</template>
