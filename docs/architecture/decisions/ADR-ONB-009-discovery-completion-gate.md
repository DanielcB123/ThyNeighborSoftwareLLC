# ADR-ONB-009: Discovery Completion Gate

## Status
Accepted

## Context
Phase 3 completion should not implicitly provision tenants.

## Decision
Use `discovery_complete` as the terminal Phase 3 onboarding state and require an explicit downstream handoff for provisioning.

## Consequences
- Tenant lifecycle remains independent from pre-client onboarding.
- Provisioning can only occur through a deliberate post-discovery process.
