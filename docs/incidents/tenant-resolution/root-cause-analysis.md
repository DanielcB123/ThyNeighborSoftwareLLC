# Root Cause Analysis — TENANCY-LOCAL-REGISTRY-001

## Primary Root Cause

Local/testing middleware behavior allowed unresolved tenant hosts to continue request execution, making platform rendering appear as tenant success when central registry resolution failed.

## Contributing Factors

1. No explicit unknown-host policy key; behavior was implicit and environment-driven.
2. Resolution caching lacked negative entries, reducing determinism for repeated misses.
3. Runtime surface attribution was not explicitly exposed as a stable marker.
4. No dedicated environment-audit command existed to consistently surface config/key drift.
5. Raw `env()` calls in runtime services introduced secondary config-path ambiguity.

## Why This Produced False Confidence

- Browser success (`200`) became disconnected from tenant-resolution correctness.
- Demo-domain checks could pass visually even when resolver state was invalid or empty.
- Frontend output could not reliably distinguish platform fallback from tenant runtime.
