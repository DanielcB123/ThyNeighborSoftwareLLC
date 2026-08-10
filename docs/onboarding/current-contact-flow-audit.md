# Current Contact Flow Audit (WEB-19)

## Scope reviewed

- `routes/web.php`
- `app/Support/Frontend/Navigation/ServerDrivenNavigationBuilder.php`
- `resources/js/core/navigation/registries/platformPublicNavigation.ts`
- `resources/js/layouts/shells/PlatformPublicShell.vue`
- `resources/js/routes/platform/index.ts`
- `resources/js/core/blocks/components/HeroBlock.vue`
- `resources/js/core/blocks/components/CTASectionBlock.vue`
- central models/migrations for reuse evaluation

## Audit summary

The previous public onboarding path was email-first:

1. Visitor clicked a Contact-oriented CTA.
2. CTA routed to `/contact`.
3. Contact hero opened `mailto:hello@webuildyouthrive.com`.
4. Discovery meeting setup happened manually outside the platform.

The new implementation replaces this with `/start-project` as the primary CTA and keeps email only as a fallback.

## Detailed inventory

| Area | Item | Previous behavior | Classification | Action |
| --- | --- | --- | --- | --- |
| Public route | `GET /contact` | Dedicated page with primary `mailto:` hero action | **Replace** | Contact page now routes primary action to `/start-project`; email remains secondary fallback |
| Public route | `GET /` (home hero CTA) | Primary CTA linked to `/contact` | **Replace** | Primary CTA now links to `/start-project` |
| Public route | `GET /` (final CTA block) | Final CTA linked to `/contact` | **Replace** | Final CTA now links to `/start-project` |
| Public route | `GET /services` (hero CTA) | Primary CTA linked to `/contact` | **Replace** | Primary CTA now links to `/start-project` |
| Public route | `GET /services` (final CTA block) | Final CTA linked to `/contact` | **Replace** | Final CTA now links to `/start-project` |
| Public route | `GET /industries` (hero CTA) | Primary CTA linked to `/contact` | **Replace** | Primary CTA now links to `/start-project` |
| Navigation (server defaults) | `ServerDrivenNavigationBuilder::defaultDefinitions()` platform item `contact` | Platform nav item pointed to `/contact` | **Replace** | Platform nav item renamed to **Start Your Project** and points to `/start-project` |
| Navigation (client fallback registry) | `platformPublicNavigation.ts` item `client-login` | Label was Contact, target `/contact` | **Replace** | Fallback item renamed and repointed to `/start-project` |
| Header CTA | `PlatformPublicShell.vue` | Header button “Book Strategy Call” linked to `/contact` | **Replace** | Header CTA now “Start Your Project” linked to `/start-project` |
| Footer CTA | `PlatformPublicShell.vue` | Footer action “Start Discovery” linked to `/contact` | **Replace** | Footer CTA now links to `/start-project` |
| Footer email availability | Public platform shell | No dedicated footer fallback email | **Keep + Relocate** | Added explicit footer fallback email; no longer primary onboarding path |
| Mailto links | Contact hero + shell | `mailto:hello@webuildyouthrive.com` used as primary conversion path | **Remove as primary / Keep as fallback** | Preserved only as secondary accessibility/unusual-case path |
| Controllers | Contact-specific controller | None (route closure only) | **Remove** | No controller migration required; onboarding now uses dedicated controllers |
| Existing inquiry forms | Public contact form | None | **Replace** | New multi-step intake flow at `/start-project` |
| Scheduling integration | Calendar/scheduler adapter | None found | **Replace later** | Discovery meeting request now captured structurally in central DB; external scheduler integration can be attached later |
| Analytics events | Contact-specific instrumentation | None found in JS/PHP | **Add later** | No existing contact analytics to preserve; onboarding analytics should be added in a follow-up task |
| Existing onboarding tables | Prospect/onboarding/discovery tables | None found | **Create** | Added central onboarding entities/tables (prospect, session, responses, discovery meeting + post-meeting structures) |
| Dead/duplicate code | Platform nav definitions duplicated in server + client fallback | Parallel definitions for resiliency | **Keep (monitor)** | Retained by design; both were updated to keep contracts aligned |

## Routes and components that previously led to Contact

- `/` hero block and CTA block
- `/services` hero block and CTA block
- `/industries` hero block
- platform header CTA button
- platform footer CTA button
- platform primary navigation item
- `/contact` page itself

## Existing entities reusable for onboarding

- `App\Shared\Database\CentralModel` for central-only persistence
- `App\Shared\Database\Concerns\HasPublicId` for stable public identifiers
- Existing central database connection and migration conventions

## Dead/duplicate contact code findings

- No dedicated Contact controller exists.
- No contact form object or request class existed.
- No legacy inquiry table existed.
- Route-generated TS (`resources/js/routes/platform/index.ts`) still contains `/contact` helper (valid because route remains available as fallback page).

## Resulting classification outcome

- **Keep:** central model conventions, fallback email availability
- **Replace:** primary contact CTAs and nav routing
- **Remove:** email-client launch as primary onboarding path
- **Relocate:** email usage moved to secondary/fallback footer + contact-page secondary action
- **Reuse:** central persistence patterns for onboarding domain entities
