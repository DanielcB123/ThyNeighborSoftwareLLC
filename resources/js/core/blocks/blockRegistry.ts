import type { Component } from "vue";
import CTASectionBlock from "@/core/blocks/components/CTASectionBlock.vue";
import FeatureGridBlock from "@/core/blocks/components/FeatureGridBlock.vue";
import HeroBlock from "@/core/blocks/components/HeroBlock.vue";
import RichTextBlock from "@/core/blocks/components/RichTextBlock.vue";
import UnknownBlock from "@/core/blocks/components/UnknownBlock.vue";
import type {
    CTASectionBlockData,
    FeatureGridBlockData,
    FeatureGridItem,
    HeroBlockData,
    KnownBlockType,
    RawContentBlock,
    RichTextBlockData,
} from "@/core/blocks/types";

type KnownBlockData =
    | HeroBlockData
    | RichTextBlockData
    | CTASectionBlockData
    | FeatureGridBlockData;

interface BlockDefinition<TData extends KnownBlockData> {
    type: KnownBlockType;
    schemaVersion: number;
    component: Component;
    parseData: (input: unknown) => TData | null;
}

export interface KnownRenderableBlock {
    id: string;
    type: KnownBlockType;
    schemaVersion: number;
    isKnown: true;
    component: Component;
    data: KnownBlockData;
}

export interface UnknownRenderableBlock {
    id: string;
    type: string;
    schemaVersion: number;
    isKnown: false;
    component: Component;
    reason: string;
}

export type RenderableBlock = KnownRenderableBlock | UnknownRenderableBlock;

function isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === "object" && value !== null;
}

function readString(
    input: Record<string, unknown>,
    key: string,
): string | null {
    const value = input[key];
    return typeof value === "string" && value.trim().length > 0
        ? value.trim()
        : null;
}

function parseHeroBlockData(input: unknown): HeroBlockData | null {
    if (!isRecord(input)) {
        return null;
    }

    const eyebrow = readString(input, "eyebrow");
    const heading = readString(input, "heading");
    const supportingText = readString(input, "supportingText");
    const primaryActionLabel = readString(input, "primaryActionLabel");
    const primaryActionPath = readString(input, "primaryActionPath");

    if (
        !eyebrow ||
        !heading ||
        !supportingText ||
        !primaryActionLabel ||
        !primaryActionPath
    ) {
        return null;
    }

    const secondaryActionLabel = readString(input, "secondaryActionLabel") ?? undefined;
    const secondaryActionPath = readString(input, "secondaryActionPath") ?? undefined;

    return {
        eyebrow,
        heading,
        supportingText,
        primaryActionLabel,
        primaryActionPath,
        secondaryActionLabel,
        secondaryActionPath,
    };
}

function parseRichTextBlockData(input: unknown): RichTextBlockData | null {
    if (!isRecord(input)) {
        return null;
    }

    const heading = readString(input, "heading");
    const rawParagraphs = input.paragraphs;

    if (!heading || !Array.isArray(rawParagraphs)) {
        return null;
    }

    const paragraphs = rawParagraphs.filter(
        (paragraph): paragraph is string =>
            typeof paragraph === "string" && paragraph.trim().length > 0,
    );

    if (paragraphs.length === 0) {
        return null;
    }

    return {
        heading,
        paragraphs,
    };
}

function parseCTASectionBlockData(input: unknown): CTASectionBlockData | null {
    if (!isRecord(input)) {
        return null;
    }

    const heading = readString(input, "heading");
    const description = readString(input, "description");
    const actionLabel = readString(input, "actionLabel");
    const actionPath = readString(input, "actionPath");

    if (!heading || !description || !actionLabel || !actionPath) {
        return null;
    }

    return {
        heading,
        description,
        actionLabel,
        actionPath,
    };
}

function parseFeatureGridItem(input: unknown): FeatureGridItem | null {
    if (!isRecord(input)) {
        return null;
    }

    const title = readString(input, "title");
    const description = readString(input, "description");

    if (!title || !description) {
        return null;
    }

    return {
        title,
        description,
        label: readString(input, "label") ?? undefined,
    };
}

function parseFeatureGridBlockData(input: unknown): FeatureGridBlockData | null {
    if (!isRecord(input)) {
        return null;
    }

    const heading = readString(input, "heading");
    const intro = readString(input, "intro");
    const rawFeatures = input.features;

    if (!heading || !intro || !Array.isArray(rawFeatures)) {
        return null;
    }

    const features = rawFeatures
        .map((entry) => parseFeatureGridItem(entry))
        .filter((entry): entry is FeatureGridItem => entry !== null);

    if (features.length === 0) {
        return null;
    }

    return {
        heading,
        intro,
        features,
    };
}

const blockDefinitions: ReadonlyMap<KnownBlockType, BlockDefinition<KnownBlockData>> =
    new Map<KnownBlockType, BlockDefinition<KnownBlockData>>([
        [
            "hero",
            {
                type: "hero",
                schemaVersion: 1,
                component: HeroBlock,
                parseData: (input) => parseHeroBlockData(input),
            },
        ],
        [
            "rich-text",
            {
                type: "rich-text",
                schemaVersion: 1,
                component: RichTextBlock,
                parseData: (input) => parseRichTextBlockData(input),
            },
        ],
        [
            "cta-section",
            {
                type: "cta-section",
                schemaVersion: 1,
                component: CTASectionBlock,
                parseData: (input) => parseCTASectionBlockData(input),
            },
        ],
        [
            "feature-grid",
            {
                type: "feature-grid",
                schemaVersion: 1,
                component: FeatureGridBlock,
                parseData: (input) => parseFeatureGridBlockData(input),
            },
        ],
    ]);

export function resolvePageBlocks(
    blocks: readonly RawContentBlock[],
): readonly RenderableBlock[] {
    return blocks.map((block) => {
        const blockDefinition = blockDefinitions.get(block.type as KnownBlockType);

        if (!blockDefinition) {
            return {
                id: block.id,
                type: block.type,
                schemaVersion: block.schemaVersion,
                isKnown: false,
                component: UnknownBlock,
                reason: "the type is not registered in this runtime",
            };
        }

        if (blockDefinition.schemaVersion !== block.schemaVersion) {
            return {
                id: block.id,
                type: block.type,
                schemaVersion: block.schemaVersion,
                isKnown: false,
                component: UnknownBlock,
                reason: `schema version ${block.schemaVersion} is unsupported`,
            };
        }

        const parsedData = blockDefinition.parseData(block.data);

        if (!parsedData) {
            return {
                id: block.id,
                type: block.type,
                schemaVersion: block.schemaVersion,
                isKnown: false,
                component: UnknownBlock,
                reason: "the block payload does not satisfy the schema",
            };
        }

        return {
            id: block.id,
            type: blockDefinition.type,
            schemaVersion: block.schemaVersion,
            isKnown: true,
            component: blockDefinition.component,
            data: parsedData,
        };
    });
}
