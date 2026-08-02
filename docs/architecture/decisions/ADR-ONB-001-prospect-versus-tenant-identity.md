# ADR-ONB-001: Prospect Versus Tenant Identity

## Status
Accepted

## Context
Pre-client onboarding participants are not yet tenant users and should not be treated as platform-authenticated operators.

## Decision
Use a dedicated prospect workspace identity model (`prospect_workspace_members`) for collaborators during onboarding and discovery.

## Consequences
- Prospect access is isolated from platform and tenant auth scopes.
- Conversion to tenant users remains an explicit post-discovery action.
