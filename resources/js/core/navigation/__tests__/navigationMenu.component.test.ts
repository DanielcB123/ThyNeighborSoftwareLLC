import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import { NavigationMenu } from '@/core/navigation/components/NavigationMenu';

describe('NavigationMenu', () => {
    it('renders nested links with semantic navigation landmarks', () => {
        const wrapper = mount(NavigationMenu, {
            props: {
                ariaLabel: 'Tenant administration',
                items: [
                    {
                        id: 'customers',
                        label: 'Customers',
                        href: 'https://smithplumbing.com/admin/customers',
                        children: [
                            {
                                id: 'customers-import',
                                label: 'Import',
                                href: 'https://smithplumbing.com/admin/customers/import',
                                children: [],
                            },
                        ],
                    },
                ],
            },
        });

        expect(wrapper.get('nav').attributes('aria-label')).toBe('Tenant administration');
        expect(wrapper.findAll('a')).toHaveLength(2);
        expect(wrapper.find('a').attributes('href')).toBe('https://smithplumbing.com/admin/customers');
    });
});
