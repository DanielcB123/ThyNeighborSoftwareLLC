declare module "*.vue" {
    import type { DefineComponent } from "vue";

    const component: DefineComponent<
        Record<string, unknown>,
        Record<string, unknown>,
        object
    >;
    export default component;
}
