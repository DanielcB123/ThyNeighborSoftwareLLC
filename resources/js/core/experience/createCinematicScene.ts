import {
    ACESFilmicToneMapping,
    AmbientLight,
    DirectionalLight,
    IcosahedronGeometry,
    Mesh,
    MeshBasicMaterial,
    MeshStandardMaterial,
    PerspectiveCamera,
    PointLight,
    Scene,
    SRGBColorSpace,
    TorusGeometry,
    TorusKnotGeometry,
    WebGLRenderer,
} from "three";

export interface CinematicSceneController {
    resize(width: number, height: number, pixelRatio: number): void;
    render(elapsedSeconds: number, pointerX: number, pointerY: number): void;
    dispose(): void;
}

export function createCinematicScene(
    canvas: HTMLCanvasElement,
): CinematicSceneController {
    const renderer = new WebGLRenderer({
        canvas,
        antialias: true,
        alpha: true,
        powerPreference: "high-performance",
    });
    const scene = new Scene();
    const camera = new PerspectiveCamera(34, 1, 0.1, 40);
    camera.position.set(0, 0, 5.25);

    renderer.outputColorSpace = SRGBColorSpace;
    renderer.toneMapping = ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;

    const keyLight = new DirectionalLight(0x9ec5ff, 2.1);
    keyLight.position.set(2.8, 2.5, 2.6);
    scene.add(keyLight);

    const rimLight = new PointLight(0xffc477, 1.6, 12);
    rimLight.position.set(-2.4, -1.6, 2.8);
    scene.add(rimLight);

    const ambientLight = new AmbientLight(0xbec8dc, 0.9);
    scene.add(ambientLight);

    const coreGeometry = new IcosahedronGeometry(1.12, 2);
    const coreMaterial = new MeshStandardMaterial({
        color: 0x5e89f7,
        emissive: 0x101f46,
        emissiveIntensity: 0.7,
        metalness: 0.32,
        roughness: 0.29,
    });
    const coreMesh = new Mesh(coreGeometry, coreMaterial);
    scene.add(coreMesh);

    const shellGeometry = new TorusKnotGeometry(1.88, 0.065, 210, 32, 2, 5);
    const shellMaterial = new MeshStandardMaterial({
        color: 0xffd181,
        emissive: 0x49331b,
        emissiveIntensity: 0.5,
        metalness: 0.74,
        roughness: 0.24,
    });
    const shellMesh = new Mesh(shellGeometry, shellMaterial);
    shellMesh.rotation.x = Math.PI / 3;
    scene.add(shellMesh);

    const haloGeometry = new TorusGeometry(2.2, 0.03, 16, 180);
    const haloMaterial = new MeshBasicMaterial({
        color: 0x97bbff,
        transparent: true,
        opacity: 0.62,
    });
    const haloMesh = new Mesh(haloGeometry, haloMaterial);
    haloMesh.rotation.x = Math.PI / 2.45;
    scene.add(haloMesh);

    return {
        resize(width, height, pixelRatio) {
            renderer.setPixelRatio(Math.min(pixelRatio || 1, 2));
            renderer.setSize(width, height, false);
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
        },

        render(elapsedSeconds, pointerX, pointerY) {
            coreMesh.rotation.y += 0.0034;
            coreMesh.rotation.x =
                0.24 + Math.sin(elapsedSeconds * 0.8) * 0.18 + pointerY * 0.07;
            coreMesh.position.x = pointerX * 0.18;
            coreMesh.position.y = Math.sin(elapsedSeconds * 1.1) * 0.08;

            shellMesh.rotation.y -= 0.0026;
            shellMesh.rotation.z += 0.0022;
            shellMesh.rotation.x = Math.PI / 3 + pointerY * 0.09;

            haloMesh.rotation.z += 0.0018;
            haloMesh.rotation.x = Math.PI / 2.45 + pointerY * 0.1;
            haloMesh.position.x = pointerX * 0.12;

            renderer.render(scene, camera);
        },

        dispose() {
            coreGeometry.dispose();
            shellGeometry.dispose();
            haloGeometry.dispose();
            coreMaterial.dispose();
            shellMaterial.dispose();
            haloMaterial.dispose();
            renderer.dispose();
        },
    };
}
