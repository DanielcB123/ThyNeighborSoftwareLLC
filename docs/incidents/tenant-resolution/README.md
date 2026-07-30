# TENANCY-LOCAL-REGISTRY-001

## Incident Scope

This incident tracks local tenancy-resolution correctness for:

- Central tenant registry integrity
- Domain-to-tenant resolution behavior
- Runtime surface attribution
- Resolution cache determinism

## Evidence Discipline

- Treat rendered pages as non-authoritative.
- Use resolver outputs, effective config, registry state, and runtime payload markers.
- Redact secrets in all logs and notes.

## Workspace Contents

- `baseline-evidence.md` — pre-remediation command baseline
- `effective-configuration.md` — env/config source conflict and duplicate audit
- `central-registry-audit.md` — tenant registry and domain mapping investigation
- `domain-resolution-audit.md` — resolver behavior and host classification audit
- `middleware-runtime-audit.md` — middleware/runtime-surface behavior audit
- `resolution-cache-audit.md` — positive/negative cache behavior audit
- `root-cause-analysis.md` — primary and contributing causes
- `remediation-log.md` — implementation actions taken
- `verification-results.md` — command/test verification evidence
- `command-output/` — raw command outputs captured during this incident
