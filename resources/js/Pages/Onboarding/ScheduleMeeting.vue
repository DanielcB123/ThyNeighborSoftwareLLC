<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    appointment: {
        type: Object,
        required: true,
    },
    token: {
        type: String,
        default: '',
    },
    timezoneOptions: {
        type: Array,
        default: () => [],
    },
    status: {
        type: String,
        default: null,
    },
    intakeSummary: {
        type: Object,
        default: null,
    },
});

const formatDateForInput = (isoString, timezone) => {
    if (!isoString) {
        return '';
    }

    const date = new Date(isoString);
    return new Intl.DateTimeFormat('en-CA', {
        timeZone: timezone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(date);
};

const formatTimeForInput = (isoString, timezone) => {
    if (!isoString) {
        return '';
    }

    const date = new Date(isoString);
    return new Intl.DateTimeFormat('en-GB', {
        timeZone: timezone,
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    }).format(date);
};

const showingRescheduleForm = ref(props.appointment.status !== 'scheduled');

const form = useForm({
    token: props.token,
    business_name: props.appointment.businessName || '',
    contact_name: props.appointment.contactName || '',
    contact_email: props.appointment.contactEmail || '',
    meeting_date: formatDateForInput(props.appointment.scheduledAt, props.appointment.timezone),
    meeting_time: formatTimeForInput(props.appointment.scheduledAt, props.appointment.timezone),
    timezone: props.appointment.timezone || Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC',
    duration_minutes: props.appointment.durationMinutes || 45,
});

const scheduledLabel = computed(() => {
    if (!props.appointment.scheduledAt) {
        return null;
    }

    const date = new Date(props.appointment.scheduledAt);

    const dateText = new Intl.DateTimeFormat('en-US', {
        timeZone: props.appointment.timezone,
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    }).format(date);

    const timeText = new Intl.DateTimeFormat('en-US', {
        timeZone: props.appointment.timezone,
        hour: 'numeric',
        minute: '2-digit',
    }).format(date);

    return `${dateText} at ${timeText} (${props.appointment.timezone})`;
});

const submit = () => {
    form.put(route('onboarding.schedule', props.appointment.publicId), {
        preserveScroll: true,
        onSuccess: () => {
            showingRescheduleForm.value = false;
        },
    });
};

const cancelMeeting = () => {
    if (!window.confirm('Cancel this onboarding meeting?')) {
        return;
    }

    form.delete(route('onboarding.cancel', props.appointment.publicId), {
        preserveScroll: true,
        data: {
            token: form.token,
        },
    });
};

const selectedProjectTypes = computed(() => {
    const value = props.intakeSummary?.project?.projectTypes;
    return Array.isArray(value) ? value : [];
});

const selectedFeatures = computed(() => {
    const value = props.intakeSummary?.features?.requestedFeatures;
    return Array.isArray(value) ? value : [];
});

const selectedAssets = computed(() => {
    const value = props.intakeSummary?.assets?.existingAssets;
    return Array.isArray(value) ? value : [];
});
</script>

<template>
    <GuestLayout>
        <Head title="Schedule Discovery Call" />

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h1 class="text-2xl font-semibold text-gray-900">Discovery Call</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Choose a date, time, and timezone for your onboarding call.
                </p>

                <div
                    v-if="status === 'scheduled'"
                    class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800"
                >
                    Your discovery call is scheduled.
                </div>

                <div
                    v-if="status === 'cancelled'"
                    class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800"
                >
                    Your discovery call has been cancelled.
                </div>
            </div>

            <div
                v-if="intakeSummary"
                class="rounded-lg border border-indigo-200 bg-indigo-50/50 p-6 shadow-sm"
            >
                <h2 class="text-lg font-semibold text-indigo-950">Client discovery briefing</h2>
                <p class="mt-1 text-sm text-indigo-900/80">
                    Submitted intake responses for meeting preparation.
                </p>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="rounded-md border border-indigo-100 bg-white p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">
                            Business
                        </p>
                        <p class="mt-2 text-sm text-gray-800">
                            {{ intakeSummary.business?.businessName || "—" }}
                        </p>
                        <p class="text-sm text-gray-600">
                            {{ intakeSummary.business?.contactName || "—" }}
                            ·
                            {{ intakeSummary.business?.businessEmail || "—" }}
                        </p>
                    </div>

                    <div class="rounded-md border border-indigo-100 bg-white p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">
                            Project type
                        </p>
                        <p class="mt-2 text-sm text-gray-800">
                            {{ selectedProjectTypes.length > 0 ? selectedProjectTypes.join(", ") : "—" }}
                        </p>
                    </div>

                    <div class="rounded-md border border-indigo-100 bg-white p-3 md:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">
                            Goals / overview
                        </p>
                        <p class="mt-2 whitespace-pre-line text-sm text-gray-700">
                            {{ intakeSummary.overview?.businessGoals || intakeSummary.overview?.whatToBuild || "—" }}
                        </p>
                    </div>

                    <div class="rounded-md border border-indigo-100 bg-white p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">
                            Requested features
                        </p>
                        <p class="mt-2 text-sm text-gray-700">
                            {{ selectedFeatures.length > 0 ? selectedFeatures.join(", ") : "—" }}
                        </p>
                    </div>

                    <div class="rounded-md border border-indigo-100 bg-white p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">
                            Existing assets
                        </p>
                        <p class="mt-2 text-sm text-gray-700">
                            {{ selectedAssets.length > 0 ? selectedAssets.join(", ") : "—" }}
                        </p>
                    </div>

                    <div class="rounded-md border border-indigo-100 bg-white p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">
                            Timeline
                        </p>
                        <p class="mt-2 text-sm text-gray-700">
                            {{ intakeSummary.timeline?.timelineExpectation || "—" }}
                            <span v-if="intakeSummary.timeline?.urgency">
                                · {{ intakeSummary.timeline.urgency }}
                            </span>
                        </p>
                    </div>

                    <div class="rounded-md border border-indigo-100 bg-white p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">
                            Additional notes
                        </p>
                        <p class="mt-2 whitespace-pre-line text-sm text-gray-700">
                            {{ intakeSummary.additional?.additionalNotes || "—" }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="appointment.status === 'scheduled' && !showingRescheduleForm"
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
            >
                <h2 class="text-lg font-semibold text-gray-900">Your discovery call is scheduled</h2>
                <p class="mt-2 text-sm text-gray-700">{{ scheduledLabel }}</p>

                <a
                    v-if="appointment.zoomJoinUrl"
                    :href="appointment.zoomJoinUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500"
                >
                    Join Zoom Meeting
                </a>

                <p v-if="appointment.zoomPasscode" class="mt-3 text-xs text-gray-500">
                    Passcode: {{ appointment.zoomPasscode }}
                </p>

                <div class="mt-6 flex items-center gap-3">
                    <SecondaryButton @click="showingRescheduleForm = true">
                        Reschedule
                    </SecondaryButton>
                    <button
                        type="button"
                        class="rounded-md border border-red-200 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50"
                        @click="cancelMeeting"
                    >
                        Cancel Meeting
                    </button>
                </div>
            </div>

            <div v-else class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <form class="space-y-4" @submit.prevent="submit">
                    <input type="hidden" v-model="form.token" />

                    <div>
                        <InputLabel for="business_name" value="Business Name" />
                        <TextInput
                            id="business_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.business_name"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.business_name" />
                    </div>

                    <div>
                        <InputLabel for="contact_name" value="Your Name" />
                        <TextInput
                            id="contact_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.contact_name"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.contact_name" />
                    </div>

                    <div>
                        <InputLabel for="contact_email" value="Email" />
                        <TextInput
                            id="contact_email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.contact_email"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.contact_email" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="meeting_date" value="Meeting Date" />
                            <TextInput
                                id="meeting_date"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="form.meeting_date"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.meeting_date" />
                        </div>

                        <div>
                            <InputLabel for="meeting_time" value="Meeting Time" />
                            <TextInput
                                id="meeting_time"
                                type="time"
                                class="mt-1 block w-full"
                                v-model="form.meeting_time"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.meeting_time" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="timezone" value="Timezone" />
                            <select
                                id="timezone"
                                v-model="form.timezone"
                                class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option
                                    v-for="timezone in timezoneOptions"
                                    :key="timezone"
                                    :value="timezone"
                                >
                                    {{ timezone }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.timezone" />
                        </div>

                        <div>
                            <InputLabel for="duration_minutes" value="Duration (minutes)" />
                            <TextInput
                                id="duration_minutes"
                                type="number"
                                min="15"
                                max="180"
                                step="15"
                                class="mt-1 block w-full"
                                v-model="form.duration_minutes"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.duration_minutes" />
                        </div>
                    </div>

                    <InputError class="mt-2" :message="form.errors.meeting" />

                    <div class="flex items-center gap-3">
                        <PrimaryButton :disabled="form.processing">
                            {{ appointment.status === 'scheduled' ? 'Update Meeting' : 'Schedule Meeting' }}
                        </PrimaryButton>
                        <SecondaryButton
                            v-if="appointment.status === 'scheduled'"
                            type="button"
                            @click="showingRescheduleForm = false"
                        >
                            Back
                        </SecondaryButton>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
