import { defineComponent, h, type PropType } from 'vue';
import type { ResolvedNavigationItem } from '@/core/navigation/navigationRegistry';

function renderItems(items: readonly ResolvedNavigationItem[]) {
    return items.map((item) =>
        h('li', { key: item.id, class: 'navigation-menu__item' }, [
            h(
                'a',
                {
                    href: item.href,
                    class: 'navigation-menu__link',
                },
                item.label,
            ),
            item.children.length > 0
                ? h('ul', { class: 'navigation-menu__children' }, renderItems(item.children))
                : null,
        ]),
    );
}

export const NavigationMenu = defineComponent({
    name: 'NavigationMenu',
    props: {
        items: {
            type: Array as PropType<readonly ResolvedNavigationItem[]>,
            required: true,
        },
        ariaLabel: {
            type: String,
            default: 'Primary navigation',
        },
    },
    setup(props) {
        return () =>
            h('nav', { 'aria-label': props.ariaLabel, class: 'navigation-menu' }, [
                h('ul', { class: 'navigation-menu__list' }, renderItems(props.items)),
            ]);
    },
});
