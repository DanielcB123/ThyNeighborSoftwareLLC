import { describe, expect, it } from 'vitest';
import { createAccessContext } from '@/core/access/accessControl';
import { resolveNavigation } from '@/core/navigation/navigationRegistry';
import { createUrlBuilder } from '@/core/url/urlBuilder';

describe('navigation browser smoke', () => {
    it('produces tenant-domain links for browser navigation', () => {
        const urlBuilder = createUrlBuilder({
            platform: {
                publicBaseUrl: 'https://webuildyouthrive.com',
                authBaseUrl: 'https://accounts.webuildyouthrive.com',
                adminBaseUrl: 'https://admin.webuildyouthrive.com',
            },
            tenant: {
                primaryBaseUrl: 'https://bellavistarestaurant.com',
                authBaseUrl: 'https://bellavistarestaurant.com',
                adminBaseUrl: 'https://bellavistarestaurant.com/admin',
                previewBaseUrl: 'https://bellavistarestaurant.com/preview',
            },
        });

        const resolved = resolveNavigation(
            [
                {
                    id: 'orders',
                    label: 'Orders',
                    target: { kind: 'internal', surface: 'tenant-admin', path: '/orders' },
                    access: { requiresAuthentication: true },
                },
            ],
            createAccessContext({ isAuthenticated: true }),
            urlBuilder,
        );

        const anchor = document.createElement('a');
        anchor.href = resolved[0].href;

        expect(anchor.hostname).toBe('bellavistarestaurant.com');
        expect(anchor.pathname).toBe('/orders');
    });
});
