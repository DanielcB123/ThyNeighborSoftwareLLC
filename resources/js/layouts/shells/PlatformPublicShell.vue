<script setup lang="ts">
import { computed } from "vue";
import { NavigationMenu } from "@/core/navigation/components/NavigationMenu";
import { usePrimaryNavigation } from "@/core/navigation/usePrimaryNavigation";
import { useUrlBuilder } from "@/core/url/urlBuilderPlugin";

const props = defineProps<{
    pageTitle: string;
    pageSummary?: string;
}>();

const urlBuilder = useUrlBuilder();
const navigationItems = usePrimaryNavigation();

const homeUrl = computed(() => urlBuilder.platformPublic("/"));
const contactUrl = computed(() => urlBuilder.platformPublic("/contact"));
</script>

<template>
    <div class="wb-shell-surface wb-platform-shell" data-surface="platform">
        <header class="wb-platform-shell__header">
            <div
                class="mx-auto flex w-full max-w-[92rem] flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-8 lg:px-10"
            >
                <a
                    :href="homeUrl"
                    class="wb-platform-shell__brand"
                    aria-label="WeBuildYouThrive home"
                >
                    WeBuildYouThrive
                </a>
                <div class="wb-platform-shell__header-right">
                    <NavigationMenu
                        :items="navigationItems"
                        aria-label="Platform primary navigation"
                    />
                    <a :href="contactUrl" class="wb-platform-shell__contact-cta">
                        Start a build
                    </a>
                </div>
            </div>
        </header>

        <main class="wb-platform-shell__main">
            <header class="sr-only">
                <h1>{{ props.pageTitle }}</h1>
                <p v-if="props.pageSummary">
                    {{ props.pageSummary }}
                </p>
            </header>
            <slot />
        </main>

        <footer class="wb-platform-shell__footer">
            <div
                class="mx-auto flex w-full max-w-[92rem] flex-wrap items-center justify-between gap-4 px-5 py-6 text-sm text-slate-400 sm:px-8 lg:px-10"
            >
                <p>© {{ new Date().getFullYear() }} WeBuildYouThrive.</p>
                <span class="wb-platform-shell__footer-tag">
                    Platform public experience
                </span>
            </div>
        </footer>
    </div>
</template>
