<script setup lang="ts">
import { computed, ref } from "vue";
import type { ResolvedNavigationItem } from "@/core/navigation/navigationRegistry";

const props = defineProps<{
    items: readonly ResolvedNavigationItem[];
    ariaLabel?: string;
}>();

const isOpen = ref(false);

const menuLabel = computed(() => props.ariaLabel ?? "Primary navigation");

function isCurrentItem(href: string): boolean {
    if (typeof window === "undefined") {
        return false;
    }

    try {
        const itemUrl = new URL(href, window.location.origin);
        return itemUrl.pathname === window.location.pathname;
    } catch {
        return false;
    }
}
</script>

<template>
    <nav :aria-label="menuLabel" class="navigation-menu">
        <div class="navigation-menu__header">
            <button
                type="button"
                class="navigation-menu__toggle md:hidden"
                :aria-expanded="isOpen ? 'true' : 'false'"
                :aria-label="`${menuLabel} menu`"
                @click="isOpen = !isOpen"
            >
                {{ isOpen ? "Close" : "Menu" }}
            </button>
        </div>
        <ul
            class="navigation-menu__list"
            :class="{
                'navigation-menu__list--open': isOpen,
            }"
        >
            <li v-for="item in items" :key="item.id" class="navigation-menu__item">
                <a
                    :href="item.href"
                    class="navigation-menu__link"
                    :aria-current="isCurrentItem(item.href) ? 'page' : undefined"
                >
                    {{ item.label }}
                </a>
                <ul v-if="item.children.length > 0" class="navigation-menu__children">
                    <li v-for="child in item.children" :key="child.id">
                        <a
                            :href="child.href"
                            class="navigation-menu__link"
                            :aria-current="isCurrentItem(child.href) ? 'page' : undefined"
                        >
                            {{ child.label }}
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</template>
