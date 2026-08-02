# ADR-ONB-002: Central Pre-Client Onboarding Storage

## Status
Accepted

## Context
Inquiry, lead, workspace, and discovery data exists before tenant provisioning and must not require tenant infrastructure.

## Decision
Store Phase 0-3 onboarding artifacts exclusively in the central database.

## Consequences
- No tenant database/domain/user is created during onboarding.
- Discovery and qualification remain auditable without provisioning side effects.
