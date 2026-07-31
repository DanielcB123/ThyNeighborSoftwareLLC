import type { CSSProperties } from "vue";
import type { TenantThemeContext } from "@/core/runtime/frontendContext";

const THEME_TOKEN_NAME_PATTERN = /^--wb-color-[a-z0-9-]+$/;

const DEFAULT_THEME_TOKENS: Readonly<Record<string, string>> = {
    "--wb-color-bg": "#ecebe7",
    "--wb-color-surface": "#fbfaf8",
    "--wb-color-surface-muted": "#f2f0ea",
    "--wb-color-border": "#d0cbc0",
    "--wb-color-border-strong": "#b5ad9d",
    "--wb-color-text": "#171715",
    "--wb-color-text-muted": "#4b4943",
    "--wb-color-primary": "#0e5c62",
    "--wb-color-primary-contrast": "#f3f6f6",
    "--wb-color-secondary": "#90502a",
    "--wb-color-danger": "#b42318",
    "--wb-color-success": "#1f7a49",
    "--wb-color-warning": "#b56b00",
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
