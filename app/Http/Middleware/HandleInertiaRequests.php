<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'publicId' => (string) ($user->public_id ?? ''),
                    'name' => (string) $user->name,
                    'email' => (string) $user->email,
                ] : null,
                'access' => $this->resolveFrontendAccessContext($request),
            ],
            'frontendRuntime' => $this->resolveFrontendRuntime($request),
        ];
    }

    /**
     * @return array{
     *   surface: string,
     *   platformUrls: array{publicBaseUrl: string, authBaseUrl: string, adminBaseUrl: string},
     *   tenant: array<string, mixed>|null
     * }
     */
    private function resolveFrontendRuntime(Request $request): array
    {
        $platformUrls = [
            'publicBaseUrl' => (string) config('frontend.platform.public_url'),
            'authBaseUrl' => (string) config('frontend.platform.auth_url'),
            'adminBaseUrl' => (string) config('frontend.platform.admin_url'),
        ];

        /** @var array<string, mixed>|null $tenantContext */
        $tenantContext = $request->attributes->get('frontendTenantContext');

        if (! is_array($tenantContext)) {
            return [
                'surface' => 'platform',
                'platformUrls' => $platformUrls,
                'tenant' => null,
            ];
        }

        return [
            'surface' => (string) Arr::get($tenantContext, 'surface', 'tenant-public'),
            'platformUrls' => $platformUrls,
            'tenant' => [
                'publicId' => (string) Arr::get($tenantContext, 'publicId'),
                'slug' => (string) Arr::get($tenantContext, 'slug'),
                'displayName' => (string) Arr::get($tenantContext, 'displayName'),
                'locale' => (string) Arr::get(
                    $tenantContext,
                    'locale',
                    config('frontend.tenant.default_locale', config('app.locale', 'en'))
                ),
                'timezone' => (string) Arr::get(
                    $tenantContext,
                    'timezone',
                    config('frontend.tenant.default_timezone', 'UTC')
                ),
                'enabledModules' => Arr::wrap(Arr::get($tenantContext, 'enabledModules', [])),
                'enabledCapabilities' => Arr::wrap(Arr::get($tenantContext, 'enabledCapabilities', [])),
                'theme' => [
                    'tokens' => Arr::wrap(Arr::get($tenantContext, 'theme.tokens', [])),
                    'logoUrl' => Arr::get($tenantContext, 'theme.logoUrl'),
                    'faviconUrl' => Arr::get($tenantContext, 'theme.faviconUrl'),
                ],
                'urls' => [
                    'primaryBaseUrl' => (string) Arr::get($tenantContext, 'urls.primaryBaseUrl'),
                    'authBaseUrl' => (string) Arr::get($tenantContext, 'urls.authBaseUrl'),
                    'adminBaseUrl' => (string) Arr::get($tenantContext, 'urls.adminBaseUrl'),
                    'previewBaseUrl' => (string) Arr::get($tenantContext, 'urls.previewBaseUrl'),
                ],
            ],
        ];
    }

    /**
     * @return array{permissions: array<int, string>, modules: array<int, string>, capabilities: array<int, string>}|null
     */
    private function resolveFrontendAccessContext(Request $request): ?array
    {
        /** @var array<string, mixed>|null $accessContext */
        $accessContext = $request->attributes->get('frontendAccessContext');

        if (! is_array($accessContext)) {
            return null;
        }

        return [
            'permissions' => array_values(Arr::wrap(Arr::get($accessContext, 'permissions', []))),
            'modules' => array_values(Arr::wrap(Arr::get($accessContext, 'modules', []))),
            'capabilities' => array_values(Arr::wrap(Arr::get($accessContext, 'capabilities', []))),
        ];
    }
}
