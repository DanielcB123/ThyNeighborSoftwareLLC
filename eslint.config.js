import js from '@eslint/js';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    {
        ignores: [
            'bootstrap/**',
            'node_modules/**',
            'public/**',
            'storage/**',
            'vendor/**',
        ],
    },
    js.configs.recommended,
    ...tseslint.configs.recommended,
    {
        files: ['resources/js/**/*.{ts,tsx}'],
        rules: {
            'no-undef': 'off',
            '@typescript-eslint/consistent-type-imports': ['error', { prefer: 'type-imports' }],
        },
    },
    {
        files: ['resources/js/**/*.d.ts'],
        rules: {
            '@typescript-eslint/no-empty-object-type': 'off',
        },
    },
);
