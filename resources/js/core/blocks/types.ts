export interface HeroBlockData {
    eyebrow: string;
    heading: string;
    supportingText: string;
    primaryActionLabel: string;
    primaryActionPath: string;
    secondaryActionLabel?: string;
    secondaryActionPath?: string;
}

export interface RichTextBlockData {
    heading: string;
    paragraphs: readonly string[];
}

export interface CTASectionBlockData {
    heading: string;
    description: string;
    actionLabel: string;
    actionPath: string;
}

export interface FeatureGridItem {
    title: string;
    description: string;
    label?: string;
}

export interface FeatureGridBlockData {
    heading: string;
    intro: string;
    features: readonly FeatureGridItem[];
}

export type KnownBlockType =
    | "hero"
    | "rich-text"
    | "cta-section"
    | "feature-grid";

export interface RawContentBlock {
    id: string;
    type: string;
    schemaVersion: number;
    data: unknown;
}
