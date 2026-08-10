# ADR-ONB-005: Discovery Response Storage

## Status
Accepted

## Context
Responses must support current answers and revision history without losing prior state.

## Decision
Store current response payloads in `discovery_responses` and append immutable revisions in `discovery_response_revisions`.

## Consequences
- Fast reads for current answers.
- Complete answer-history traceability for review and audit.
