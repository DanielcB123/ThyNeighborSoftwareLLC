<?php

declare(strict_types=1);

namespace App\Support\Frontend\Navigation;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class ServerDrivenNavigationBuilder
{
    /**
     * @param  array<string, mixed>  $frontendRuntime
     * @param  array{permissions: array<int, string>, modules: array<int, string>, capabilities: array<int, string>, roles: array<int, string>}|null  $accessContext
     * @return array{
     *   surface: string,
     *   primary: array<int, array<string, mixed>>
     * }
     */
    public function build(
        Request $request,
        array $frontendRuntime,
        ?array $accessContext,
    ): array {
        $surface = (string) Arr::get($frontendRuntime, 'surface', 'platform');
        $definitions = $this->resolveDefinitions($request);
        $surfaceDefinitions = Arr::wrap(Arr::get($definitions, $surface, []));
        $effectiveAccessContext = $this->resolveEffectiveAccessContext(
            $request,
            $frontendRuntime,
            $accessContext
        );

        return [
            'surface' => $surface,
            'primary' => $this->normalizeVisibleItems(
                $surfaceDefinitions,
                $request,
                $effectiveAccessContext
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveDefinitions(Request $request): array
    {
        /** @var array<string, mixed>|null $customNavigationDefinitions */
        $customNavigationDefinitions = $request->attributes->get('frontendNavigationConfig');
        $definitions = $this->defaultDefinitions();

        if (! is_array($customNavigationDefinitions)) {
            return $definitions;
        }

        foreach ($customNavigationDefinitions as $surface => $surfaceDefinitions) {
            if (! is_string($surface) || ! is_array($surfaceDefinitions)) {
                continue;
            }

            $definitions[$surface] = $surfaceDefinitions;
        }

        return $definitions;
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultDefinitions(): array
    {
        return [
            'platform' => [
                [
                    'id' => 'home',
                    'label' => 'Home',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'platform-public',
                        'path' => '/',
                    ],
                ],
                [
                    'id' => 'services',
                    'label' => 'Services',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'platform-public',
                        'path' => '/services',
                    ],
                ],
                [
                    'id' => 'industries',
                    'label' => 'Industries',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'platform-public',
                        'path' => '/industries',
                    ],
                ],
                [
                    'id' => 'contact',
                    'label' => 'Contact',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'platform-public',
                        'path' => '/contact',
                    ],
                ],
            ],
            'tenant-public' => [
                [
                    'id' => 'home',
                    'label' => 'Home',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'tenant-public',
                        'path' => '/',
                    ],
                ],
                [
                    'id' => 'services',
                    'label' => 'Services',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'tenant-public',
                        'path' => '/services',
                    ],
                ],
                [
                    'id' => 'request-service',
                    'label' => 'Request Service',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'tenant-public',
                        'path' => '/request-service',
                    ],
                    'access' => [
                        'anyModules' => ['dispatch', 'crm'],
                    ],
                ],
                [
                    'id' => 'customer-portal',
                    'label' => 'Customer Portal',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'tenant-auth',
                        'path' => '/login',
                    ],
                ],
            ],
            'tenant-auth' => [
                [
                    'id' => 'home',
                    'label' => 'Home',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'tenant-public',
                        'path' => '/',
                    ],
                ],
                [
                    'id' => 'customer-portal',
                    'label' => 'Customer Portal Login',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'tenant-auth',
                        'path' => '/login',
                    ],
                ],
            ],
            'tenant-admin' => [
                [
                    'id' => 'overview',
                    'label' => 'Overview',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'tenant-admin',
                        'path' => '/dashboard',
                    ],
                    'access' => [
                        'requiresAuthentication' => true,
                    ],
                ],
                [
                    'id' => 'customers',
                    'label' => 'Customers',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'tenant-admin',
                        'path' => '/customers',
                    ],
                    'access' => [
                        'requiresAuthentication' => true,
                        'allPermissions' => ['customers.view'],
                    ],
                ],
                [
                    'id' => 'dispatch',
                    'label' => 'Dispatch',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'tenant-admin',
                        'path' => '/dispatch',
                    ],
                    'access' => [
                        'requiresAuthentication' => true,
                        'allModules' => ['dispatch'],
                        'allPermissions' => ['dispatch.view'],
                    ],
                ],
                [
                    'id' => 'billing',
                    'label' => 'Billing',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'tenant-admin',
                        'path' => '/billing',
                    ],
                    'access' => [
                        'requiresAuthentication' => true,
                        'allCapabilities' => ['billing'],
                        'allPermissions' => ['billing.view'],
                        'anyRoles' => ['owner', 'finance-manager'],
                    ],
                ],
            ],
            'tenant-preview' => [
                [
                    'id' => 'preview-home',
                    'label' => 'Preview Home',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'tenant-preview',
                        'path' => '/',
                    ],
                ],
                [
                    'id' => 'return-admin',
                    'label' => 'Return to Admin',
                    'target' => [
                        'kind' => 'internal',
                        'surface' => 'tenant-admin',
                        'path' => '/dashboard',
                    ],
                    'access' => [
                        'requiresAuthentication' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $frontendRuntime
     * @param  array{permissions: array<int, string>, modules: array<int, string>, capabilities: array<int, string>, roles: array<int, string>}|null  $accessContext
     * @return array{
     *   isAuthenticated: bool,
     *   permissions: array<int, string>,
     *   modules: array<int, string>,
     *   capabilities: array<int, string>,
     *   roles: array<int, string>
     * }
     */
    private function resolveEffectiveAccessContext(
        Request $request,
        array $frontendRuntime,
        ?array $accessContext,
    ): array {
        $tenantModules = Arr::wrap(Arr::get($frontendRuntime, 'tenant.enabledModules', []));
        $tenantCapabilities = Arr::wrap(
            Arr::get($frontendRuntime, 'tenant.enabledCapabilities', [])
        );
        $permissions = Arr::wrap(Arr::get($accessContext, 'permissions', []));
        $modules = Arr::wrap(Arr::get($accessContext, 'modules', $tenantModules));
        $capabilities = Arr::wrap(
            Arr::get($accessContext, 'capabilities', $tenantCapabilities)
        );
        $roles = Arr::wrap(Arr::get($accessContext, 'roles', []));

        return [
            'isAuthenticated' => $request->user() !== null,
            'permissions' => array_values(array_unique(array_map('strval', $permissions))),
            'modules' => array_values(array_unique(array_map('strval', $modules))),
            'capabilities' => array_values(array_unique(array_map('strval', $capabilities))),
            'roles' => array_values(array_unique(array_map('strval', $roles))),
        ];
    }

    /**
     * @param  array<int, mixed>  $definitions
     * @param  array{
     *   isAuthenticated: bool,
     *   permissions: array<int, string>,
     *   modules: array<int, string>,
     *   capabilities: array<int, string>,
     *   roles: array<int, string>
     * }  $effectiveAccessContext
     * @return array<int, array<string, mixed>>
     */
    private function normalizeVisibleItems(
        array $definitions,
        Request $request,
        array $effectiveAccessContext,
    ): array {
        $normalizedItems = [];

        foreach ($definitions as $definition) {
            if (! is_array($definition)) {
                continue;
            }

            if (! $this->passesAccess(
                Arr::get($definition, 'access'),
                $request,
                $effectiveAccessContext
            )) {
                continue;
            }

            $id = trim((string) Arr::get($definition, 'id', ''));
            $label = trim((string) Arr::get($definition, 'label', ''));
            $target = Arr::get($definition, 'target');

            if ($id === '' || $label === '' || ! is_array($target)) {
                continue;
            }

            $normalizedTarget = $this->normalizeTarget($target);

            if ($normalizedTarget === null) {
                continue;
            }

            $children = Arr::wrap(Arr::get($definition, 'children', []));
            $normalizedChildren = $this->normalizeVisibleItems(
                $children,
                $request,
                $effectiveAccessContext
            );

            $normalizedItems[] = [
                'id' => $id,
                'label' => $label,
                'target' => $normalizedTarget,
                'access' => $this->normalizeAccessForClient(Arr::get($definition, 'access')),
                'children' => $normalizedChildren,
            ];
        }

        return $normalizedItems;
    }

    /**
     * @param  mixed  $accessRequirements
     * @return array<string, mixed>|null
     */
    private function normalizeAccessForClient(mixed $accessRequirements): ?array
    {
        if (! is_array($accessRequirements)) {
            return null;
        }

        $normalizedAccess = [
            'requiresAuthentication' => Arr::get($accessRequirements, 'requiresAuthentication'),
            'allPermissions' => Arr::wrap(Arr::get($accessRequirements, 'allPermissions', [])),
            'anyPermissions' => Arr::wrap(Arr::get($accessRequirements, 'anyPermissions', [])),
            'allModules' => Arr::wrap(Arr::get($accessRequirements, 'allModules', [])),
            'anyModules' => Arr::wrap(Arr::get($accessRequirements, 'anyModules', [])),
            'allCapabilities' => Arr::wrap(Arr::get($accessRequirements, 'allCapabilities', [])),
            'anyCapabilities' => Arr::wrap(Arr::get($accessRequirements, 'anyCapabilities', [])),
            'allRoles' => Arr::wrap(Arr::get($accessRequirements, 'allRoles', [])),
            'anyRoles' => Arr::wrap(Arr::get($accessRequirements, 'anyRoles', [])),
        ];

        return collect($normalizedAccess)
            ->filter(function (mixed $value, string $key): bool {
                if ($key === 'requiresAuthentication') {
                    return $value === true;
                }

                return is_array($value) && $value !== [];
            })
            ->all();
    }

    /**
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>|null
     */
    private function normalizeTarget(array $target): ?array
    {
        $kind = (string) Arr::get($target, 'kind', '');

        if ($kind === 'external') {
            $href = trim((string) Arr::get($target, 'href', ''));

            if ($href === '') {
                return null;
            }

            return [
                'kind' => 'external',
                'href' => $href,
            ];
        }

        if ($kind === 'internal') {
            $surface = trim((string) Arr::get($target, 'surface', ''));
            $path = trim((string) Arr::get($target, 'path', ''));

            if ($surface === '' || $path === '') {
                return null;
            }

            return [
                'kind' => 'internal',
                'surface' => $surface,
                'path' => $path,
                'options' => Arr::get($target, 'options'),
            ];
        }

        return null;
    }

    /**
     * @param  mixed  $accessRequirements
     * @param  array{
     *   isAuthenticated: bool,
     *   permissions: array<int, string>,
     *   modules: array<int, string>,
     *   capabilities: array<int, string>,
     *   roles: array<int, string>
     * }  $effectiveAccessContext
     */
    private function passesAccess(
        mixed $accessRequirements,
        Request $request,
        array $effectiveAccessContext,
    ): bool {
        if (! is_array($accessRequirements)) {
            return true;
        }

        if (
            (bool) Arr::get($accessRequirements, 'requiresAuthentication', false) &&
            ! $effectiveAccessContext['isAuthenticated']
        ) {
            return false;
        }

        return $this->includesAll(
            $effectiveAccessContext['permissions'],
            Arr::wrap(Arr::get($accessRequirements, 'allPermissions', []))
        ) &&
            $this->includesAny(
                $effectiveAccessContext['permissions'],
                Arr::wrap(Arr::get($accessRequirements, 'anyPermissions', []))
            ) &&
            $this->includesAll(
                $effectiveAccessContext['modules'],
                Arr::wrap(Arr::get($accessRequirements, 'allModules', []))
            ) &&
            $this->includesAny(
                $effectiveAccessContext['modules'],
                Arr::wrap(Arr::get($accessRequirements, 'anyModules', []))
            ) &&
            $this->includesAll(
                $effectiveAccessContext['capabilities'],
                Arr::wrap(Arr::get($accessRequirements, 'allCapabilities', []))
            ) &&
            $this->includesAny(
                $effectiveAccessContext['capabilities'],
                Arr::wrap(Arr::get($accessRequirements, 'anyCapabilities', []))
            ) &&
            $this->includesAll(
                $effectiveAccessContext['roles'],
                Arr::wrap(Arr::get($accessRequirements, 'allRoles', []))
            ) &&
            $this->includesAny(
                $effectiveAccessContext['roles'],
                Arr::wrap(Arr::get($accessRequirements, 'anyRoles', []))
            ) &&
            $this->passesGates(
                $request,
                Arr::wrap(Arr::get($accessRequirements, 'allGates', [])),
                Arr::wrap(Arr::get($accessRequirements, 'anyGates', []))
            );
    }

    /**
     * @param  array<int, string>  $values
     * @param  array<int, string>  $requiredValues
     */
    private function includesAll(array $values, array $requiredValues): bool
    {
        $required = array_values(array_filter(array_map('trim', $requiredValues)));

        if ($required === []) {
            return true;
        }

        $valueSet = array_flip($values);

        foreach ($required as $requiredValue) {
            if (! array_key_exists($requiredValue, $valueSet)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, string>  $values
     * @param  array<int, string>  $requiredValues
     */
    private function includesAny(array $values, array $requiredValues): bool
    {
        $required = array_values(array_filter(array_map('trim', $requiredValues)));

        if ($required === []) {
            return true;
        }

        $valueSet = array_flip($values);

        foreach ($required as $requiredValue) {
            if (array_key_exists($requiredValue, $valueSet)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $allGates
     * @param  array<int, string>  $anyGates
     */
    private function passesGates(
        Request $request,
        array $allGates,
        array $anyGates,
    ): bool {
        $allGateNames = array_values(array_filter(array_map('trim', $allGates)));
        $anyGateNames = array_values(array_filter(array_map('trim', $anyGates)));
        $user = $request->user();

        if (($allGateNames !== [] || $anyGateNames !== []) && $user === null) {
            return false;
        }

        if ($allGateNames !== []) {
            foreach ($allGateNames as $gateName) {
                if (! Gate::forUser($user)->allows($gateName)) {
                    return false;
                }
            }
        }

        if ($anyGateNames === []) {
            return true;
        }

        foreach ($anyGateNames as $gateName) {
            if (Gate::forUser($user)->allows($gateName)) {
                return true;
            }
        }

        return false;
    }
}
