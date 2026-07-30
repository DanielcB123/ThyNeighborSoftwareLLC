# WEB-11 Foundation Implementation Notes

This repository started from a stock Laravel skeleton. The full ThyNeighborSoftware implementation manifest describes a much broader modular monolith than currently exists in this codebase.

## Implemented in this issue

- Added shared DB foundation classes for explicit central/tenant boundaries:
  - `App\Shared\Database\CentralModel`
  - `App\Shared\Database\TenantModel`
  - `App\Shared\Database\ConnectionTransactionManager`
- Added public identifier foundation:
  - `App\Shared\Identifiers\Contracts\PublicIdGenerator`
  - `App\Shared\Identifiers\UlidPublicIdGenerator`
  - `App\Shared\Database\Concerns\HasPublicId`
- Bound `PublicIdGenerator` in `AppServiceProvider`.
- Enforced a Laravel morph map with the currently-available mapped model key (`user`).
- Added `public_id` to `users` with `CHAR(26)` and a named unique index.
- Updated initial migrations with:
  - explicit MySQL table defaults (`InnoDB`, `utf8mb4`, `utf8mb4_0900_ai_ci`)
  - explicit index and FK names where relevant
  - `users.deleted_at` soft-delete support
- Added central/tenant DB connection config stanzas.
- Added tests for public ID generation behavior and explicit connection transaction routing.

## Intentional deviations from the full manifest

- Only the shared foundation slice was implemented; full central business modules, tenant runtime modules, frontend platform/tenant surfaces, and additional domain entities are not present in this repository yet.
- Morph map currently includes only `user` because other domain models from the manifest are not yet implemented.
- `User` remains on Laravel's `Authenticatable` base class instead of inheriting `CentralModel` to avoid coupling auth scaffolding to the new base model before wider platform modules are introduced.
