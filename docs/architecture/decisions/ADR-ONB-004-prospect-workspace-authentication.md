# ADR-ONB-004: Prospect Workspace Authentication

## Status
Accepted

## Context
Prospects need resumable secure access without platform account creation.

## Decision
Use discovery session access tokens for workflow continuity and map collaborator identity to workspace membership records.

## Consequences
- Onboarding can continue asynchronously without platform login.
- Session-token and workspace-member scopes remain separate from tenant auth.
