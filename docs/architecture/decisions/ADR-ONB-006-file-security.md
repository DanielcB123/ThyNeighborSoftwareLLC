# ADR-ONB-006: Prospect File Security

## Status
Accepted

## Context
Prospect files may include sensitive project material and must remain access-controlled and scan-trackable.

## Decision
Persist file metadata in `prospect_files` with explicit visibility, category, and scan status attributes.

## Consequences
- File access can be enforced at the workspace boundary.
- Security workflows can track scanning and file lifecycle states.
