<?php

declare(strict_types=1);

namespace App\Demo\Data;

use InvalidArgumentException;

final readonly class DemoTenantScenarioData
{
    /**
     * @param  list<array{key:string,name:string,scope:string,parent:?string}>  $organizationUnits
     * @param  list<string>  $leadSources
     * @param  list<string>  $expenseVendors
     * @param  list<array<string, mixed>>  $users
     */
    public function __construct(
        public string $key,
        public int $weight,
        public string $slug,
        public string $displayName,
        public string $publicId,
        public string $domain,
        public string $databaseName,
        public string $vertical,
        public string $locale,
        public string $timezone,
        public string $locationPrefix,
        public array $organizationUnits,
        public array $leadSources,
        public array $expenseVendors,
        public array $users,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $required = [
            'key',
            'slug',
            'display_name',
            'public_id',
            'domain',
            'database_name',
        ];

        foreach ($required as $requiredField) {
            if (! isset($payload[$requiredField]) || trim((string) $payload[$requiredField]) === '') {
                throw new InvalidArgumentException(sprintf(
                    'Demo tenant scenario is missing required field "%s".',
                    $requiredField
                ));
            }
        }

        return new self(
            key: (string) $payload['key'],
            weight: max(1, (int) ($payload['weight'] ?? 1)),
            slug: (string) $payload['slug'],
            displayName: (string) $payload['display_name'],
            publicId: (string) $payload['public_id'],
            domain: strtolower((string) $payload['domain']),
            databaseName: strtolower((string) $payload['database_name']),
            vertical: (string) ($payload['vertical'] ?? 'service-business'),
            locale: (string) ($payload['locale'] ?? 'en'),
            timezone: (string) ($payload['timezone'] ?? 'UTC'),
            locationPrefix: strtoupper((string) ($payload['location_prefix'] ?? 'LOC')),
            organizationUnits: array_values(array_filter(
                is_array($payload['organization_units'] ?? null) ? $payload['organization_units'] : [],
                static fn (mixed $unit): bool => is_array($unit)
            )),
            leadSources: array_values(array_map(
                'strval',
                is_array($payload['lead_sources'] ?? null) ? $payload['lead_sources'] : ['website']
            )),
            expenseVendors: array_values(array_map(
                'strval',
                is_array($payload['expense_vendors'] ?? null) ? $payload['expense_vendors'] : ['General Vendor']
            )),
            users: array_values(array_filter(
                is_array($payload['users'] ?? null) ? $payload['users'] : [],
                static fn (mixed $user): bool => is_array($user)
            )),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'weight' => $this->weight,
            'slug' => $this->slug,
            'display_name' => $this->displayName,
            'public_id' => $this->publicId,
            'domain' => $this->domain,
            'database_name' => $this->databaseName,
            'vertical' => $this->vertical,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'location_prefix' => $this->locationPrefix,
            'organization_units' => $this->organizationUnits,
            'lead_sources' => $this->leadSources,
            'expense_vendors' => $this->expenseVendors,
            'users' => $this->users,
        ];
    }
}
