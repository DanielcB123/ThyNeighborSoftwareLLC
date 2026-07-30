import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    test: {
        include: ['resources/js/**/__tests__/*.test.ts'],
        environment: 'node',
        environmentMatchGlobs: [
            ['**/*.component.test.ts', 'jsdom'],
            ['**/*.browser.test.ts', 'jsdom'],
            ['**/*.a11y.test.ts', 'jsdom'],
        ],
        coverage: {
            provider: 'v8',
            reporter: ['text', 'lcov'],
        },
    },
});
