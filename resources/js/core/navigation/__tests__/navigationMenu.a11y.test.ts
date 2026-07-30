import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import { axe, toHaveNoViolations } from 'vitest-axe';
import { NavigationMenu } from '@/core/navigation/components/NavigationMenu';

expect.extend(toHaveNoViolations);

describe('NavigationMenu accessibility', () => {
    it('renders accessible nav markup', async () => {
        const wrapper = mount(NavigationMenu, {
            props: {
                items: [
                    {
                        id: 'home',
                        label: 'Home',
                        href: 'https://smithplumbing.com',
                        children: [],
                    },
                    {
                        id: 'services',
                        label: 'Services',
                        href: 'https://smithplumbing.com/services',
                        children: [],
                    },
                ],
            },
        });

        const results = await axe(wrapper.element);
        expect(results).toHaveNoViolations();
    });
});
