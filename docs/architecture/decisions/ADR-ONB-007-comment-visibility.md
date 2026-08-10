# ADR-ONB-007: Comment and Message Visibility

## Status
Accepted

## Context
Workspace conversations include both collaborator-visible messages and potentially internal-only discussion.

## Decision
Represent message visibility explicitly (`prospect_messages.visibility`) and keep threads scoped to a workspace.

## Consequences
- Internal/private messaging can be enforced without separate storage systems.
- Visibility can be audited and filtered by role/context.
