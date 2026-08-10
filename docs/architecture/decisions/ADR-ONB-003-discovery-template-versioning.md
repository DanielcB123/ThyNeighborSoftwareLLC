# ADR-ONB-003: Discovery Template Versioning

## Status
Accepted

## Context
Discovery prompts evolve over time and historic sessions must remain interpretable against the exact question contract used at submission time.

## Decision
Adopt immutable `discovery_template_versions` linked from `discovery_sessions`.

## Consequences
- New question structures are introduced by publishing new versions.
- Existing sessions stay pinned to their original template version.
