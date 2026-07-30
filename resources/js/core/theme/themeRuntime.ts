import type { CSSProperties } from "vue";
import type { TenantThemeContext } from "@/core/runtime/frontendContext";

const THEME_TOKEN_NAME_PATTERN = /^--wb-color-[a-z0-9-]+$/;

const DEFAULT_THEME_TOKENS: Readonly<Record<string, string>> = {
    "--wb-color-bg": "#f7f8fc",
    "--wb-color-surface": "#ffffff",
    "--wb-color-surface-muted": "#eef2ff",
    "--wb-color-border": "#d4d9e8",
    "--wb-color-text": "#0f172a",
    "--wb-color-text-muted": "#475569",
    "--wb-color-primary": "#1d4ed8",
    "--wb-color-primary-contrast": "#ffffff",
    "--wb-color-secondary": "#0ea5e9",
    "--wb-color-danger": "#dc2626",
    "--wb-color-success": "#15803d",
    "--wb-color-warning": "#d97706",
};

export function resolveTenantThemeTokens(
    tenantTheme: TenantThemeContext | null | undefined,
): Readonly<Record<string, string>> {
    if (!tenantTheme?.tokens) {
        return DEFAULT_THEME_TOKENS;
    }

    const sanitizedThemeTokens = Object.entries(tenantTheme.tokens).reduce<
        Record<string, string>
    >((tokens, [tokenName, tokenValue]) => {
        if (
            THEME_TOKEN_NAME_PATTERN.test(tokenName) &&
            tokenValue.trim().length > 0
        ) {
            tokens[tokenName] = tokenValue.trim();
        }

        return tokens;
    }, {});

    return {
        ...DEFAULT_THEME_TOKENS,
        ...sanitizedThemeTokens,
    };
}

export function resolveTenantThemeStyle(
    tenantTheme: TenantThemeContext | null | undefined,
): CSSProperties {
    const tokenMap = resolveTenantThemeTokens(tenantTheme);
    const styleMap: Record<string, string> = {};

    for (const [tokenName, tokenValue] of Object.entries(tokenMap)) {
        styleMap[tokenName] = tokenValue;
    }

    return styleMap as CSSProperties;
}
