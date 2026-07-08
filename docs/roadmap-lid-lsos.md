# LID Core Framework and LSOS Roadmap

## Project Vision

LID Core Framework is the stable foundation layer for Large Studio digital products. It should provide the reusable WordPress plugin infrastructure, shared contracts, safe defaults, and site-facing modules that can be trusted across releases.

LSOS, the Large Studio Operating System, is the operational application layer built on top of LID Core. It should coordinate business workflows such as CRM, logistics, tracking, automation, reporting, and AI-assisted operations without pushing product-specific complexity into the core framework.

The long-term direction is a clean split:

- LID Core stays small, stable, reusable, and release-safe.
- LSOS grows as a modular operating system for studio and business workflows.
- Shared conventions make LSOS modules predictable without turning Core into a catch-all application.

## LID Core vs LSOS

| Area | LID Core Framework | LSOS |
| --- | --- | --- |
| Purpose | Foundation, shared contracts, stable site features | Business operations and workflow automation |
| Release style | Conservative, backward compatible, low churn | Faster module growth with feature flags and staged rollouts |
| Audience | Developers, site administrators, frontend integrations | Operators, managers, sales, logistics, support, automation teams |
| Data ownership | Plugin settings, display defaults, shared framework data | CRM records, workflow state, tracking events, operational entities |
| UI scope | Admin settings, site widgets, shortcode output | Dashboards, work queues, CRM screens, automation panels |
| Examples | Autoloader, settings, BlogRenderer, shortcode, Elementor bridge | Lead pipeline, shipment tracking, AI assistant, task routing |

## Current v1.0.0 Status

IranianDubai Core has reached a production-ready v1.0.0 foundation with:

- PHP 8.2 and WordPress 6.7+ compatibility.
- PSR-4 autoloading for the `IDB\` namespace.
- Blog rendering centralized in `IDB\Frontend\BlogRenderer`.
- Query creation centralized in `IDB\Blog\Query`.
- `[idb_blog]` shortcode support.
- Elementor Blog widget support using the shared renderer.
- AJAX pagination, category filtering, and search with non-JS fallbacks.
- Admin settings for blog defaults.
- Settings import/export with nonce and capability checks.
- Safe cache invalidation for blog output.
- Localization-ready strings and Persian admin fallback handling.

v1.0.0 should be treated as the stable Core baseline. Future work should add capabilities without weakening the renderer/query/settings boundaries established in this release.

## LID Core Modules Roadmap

Core modules should remain reusable across projects and avoid business-specific assumptions.

Recommended Core module families:

- `Core`: bootstrap, lifecycle, requirements, module contracts, autoloader.
- `Admin`: settings, import/export, admin notices, help documentation.
- `Frontend`: shared frontend rendering and asset registration.
- `Blog`: query builder, shortcode registration, blog defaults.
- `Elementor`: optional widget bridges backed by Core renderers.
- `Assets`: shared asset registration conventions if asset needs grow.
- `I18n`: translation helpers only if localization grows beyond current fallback needs.
- `Cache`: shared cache keys and invalidation helpers if multiple modules need caching.
- `Security`: shared nonce, capability, sanitization helpers only if duplication becomes meaningful.

Core should prefer small, boring modules. A module belongs in Core only when at least two downstream features can reuse it safely.

## LSOS Modules Roadmap

LSOS modules should model operational workflows and business entities.

Recommended LSOS module families:

- `CRM`: leads, contacts, accounts, deals, pipeline stages, follow-up history.
- `Operations`: tasks, assignments, SLAs, approvals, internal work queues.
- `Logistics`: shipments, routes, carriers, delivery milestones, exceptions.
- `Tracking`: event streams, status timelines, audit trails, customer-visible tracking.
- `Automation`: triggers, actions, rules, schedules, notifications.
- `AI`: assistant workflows, summarization, lead scoring, routing recommendations.
- `Reporting`: dashboards, KPIs, exports, operational health metrics.
- `Integrations`: APIs, webhooks, third-party sync, external credentials.
- `Notifications`: email, SMS, WhatsApp, admin alerts, escalation rules.
- `Permissions`: LSOS roles, operational capabilities, record-level access.

LSOS should be free to evolve quickly, but each module should have clear ownership of data, screens, permissions, and side effects.

## Version Strategy

Use semantic versioning with conservative Core changes:

- `1.0.x`: production hotfixes, security fixes, bug fixes, documentation corrections.
- `1.1.x`: safe Core enhancements that preserve existing public behavior.
- `1.2.x`: new optional Core modules or integrations with migration-free defaults.
- `1.3.x`: broader admin or developer experience improvements that remain backward compatible.
- `2.0.0`: intentional breaking changes, namespace changes, data migrations, or framework contract revisions.

LSOS may use faster minor versions, but every LSOS version should declare its required LID Core version.

Example compatibility:

```text
LSOS 1.0 requires LID Core >= 1.1 < 2.0
LSOS 1.2 requires LID Core >= 1.2 < 2.0
LSOS 2.0 requires LID Core >= 2.0
```

## Branch Strategy

Recommended branch model:

- `main`: stable released code only.
- `release/vX.Y.Z`: release preparation, QA fixes, version metadata.
- `sprint-N-description`: planned sprint work.
- `hotfix-description`: urgent production fixes from the current stable base.
- `feature/lid-module-name`: focused Core module work.
- `feature/lsos-module-name`: focused LSOS module work.
- `docs/topic-name`: documentation-only work.

Rules:

- Do not merge LSOS feature work into Core release branches.
- Do not mix documentation cleanup with behavioral hotfixes unless the doc update directly explains the fix.
- Hotfix branches should remain small and easy to cherry-pick.
- Release branches should not introduce large architecture changes.

## Module Naming Conventions

Recommended PHP namespaces:

```text
LID\Core
LID\Admin
LID\Frontend
LID\Blog
LID\Elementor
LSOS\CRM
LSOS\Logistics
LSOS\Tracking
LSOS\Automation
LSOS\AI
```

For the current IranianDubai Core plugin, existing `IDB\` namespaces should remain stable until a deliberate v2.0 migration.

Recommended slugs and handles:

- Core plugin slug: `lid-core` for future framework extraction.
- Current plugin slug: `iraniandubai-core`.
- LSOS plugin slug: `lsos`.
- Module option keys: `lid_core_*` or `lsos_*`.
- Asset handles: `lid-core-*` or `lsos-*`.
- AJAX actions: `lid_core_*` or `lsos_*`.
- REST namespaces: `lid-core/v1` and `lsos/v1`.

Avoid ambiguous names such as `manager`, `helper`, or `system` unless the class is clearly scoped by namespace and responsibility.

## Recommended Folder Architecture

Future LID Core layout:

```text
lid-core/
  assets/
    css/
    js/
  docs/
  languages/
  src/
    Admin/
    Assets/
    Blog/
    Cache/
    Core/
    Elementor/
    Frontend/
    I18n/
    Security/
  templates/
  tests/
```

Future LSOS layout:

```text
lsos/
  assets/
    css/
    js/
  docs/
  languages/
  src/
    AI/
    Automation/
    CRM/
    Core/
    Integrations/
    Logistics/
    Notifications/
    Operations/
    Permissions/
    Reporting/
    Tracking/
  templates/
  tests/
```

Shared rule: folders should reflect product boundaries, not technical convenience. If a file needs to know too much about another module, the contract between those modules is probably missing.

## Development Rules

Core rules:

- Keep architecture stable.
- Keep renderers as the single rendering source for their domain.
- Keep query builders as the single query source for their domain.
- Prefer progressive enhancement for frontend behavior.
- Preserve non-JS fallbacks.
- Keep settings sanitized, bounded, and backward compatible.
- Do not add user-specific data to public cache output.
- Keep Elementor and shortcode behavior aligned through shared renderers.
- Avoid heavy dependencies unless the value is clear and long-term.

LSOS rules:

- Model business entities explicitly.
- Keep workflow state auditable.
- Treat automation as reversible or reviewable where possible.
- Make permissions explicit before exposing operational screens.
- Store external integration secrets safely.
- Log important state transitions without leaking sensitive data.
- Keep AI suggestions distinguishable from human decisions.
- Design modules so they can be disabled without corrupting shared data.

Shared rules:

- PHP 8.2+ compatibility.
- WordPress Coding Standards.
- PSR-4 autoloading.
- Capability checks for admin and operational actions.
- Nonce checks for form submissions.
- REST permission callbacks for APIs.
- Escaping on output, sanitization on input.
- No debug output in production.

## What Belongs in Core

Core should include:

- Bootstrap and lifecycle management.
- Autoloading and module registration.
- Shared settings infrastructure.
- Reusable admin patterns.
- Reusable frontend rendering primitives.
- Stable shortcode and Elementor bridges.
- Safe caching primitives.
- Shared localization utilities.
- Shared security helpers when repetition justifies them.
- Site-facing modules that are generic enough to reuse.

Core should answer: "What foundation do all products need?"

## What Belongs in LSOS

LSOS should include:

- CRM workflows and customer records.
- Lead management and qualification.
- Operational dashboards.
- Work queues and task routing.
- Logistics tracking and shipment state.
- Automation rules and action history.
- AI-assisted recommendations and summaries.
- Reporting and exports tied to operations.
- Third-party operational integrations.
- Role-specific screens and permissions.

LSOS should answer: "How does the business operate day to day?"

## What Should Never Be Mixed

Do not mix:

- CRM records into Core settings.
- Shipment state into frontend rendering classes.
- AI prompt logic into shortcode or Elementor widgets.
- Business workflow rules into Core bootstrap.
- LSOS dashboards into Core admin settings.
- Customer-specific automation into reusable Core modules.
- Public cache output with user-specific operational data.
- External API secrets with display configuration.
- Data migrations with unrelated UI polish.
- Breaking namespace changes with patch releases.

If a feature requires business context, it belongs in LSOS. If a feature is reusable infrastructure, it may belong in Core.

## Roadmap

### v1.1

LID Core:

- Stabilize developer documentation.
- Add optional internal diagnostics for configuration health.
- Improve translation file workflow if Persian and English content expands.
- Add focused tests around settings sanitization and renderer defaults.
- Review dead legacy files and remove only with clear migration notes.

LSOS:

- Define initial product requirements.
- Draft entity models for contacts, leads, tasks, shipments, and events.
- Define required Core version.
- Build proof-of-concept module registration.

### v1.2

LID Core:

- Introduce shared asset registration helpers if duplication appears.
- Consider shared cache helper only if more modules need cache invalidation.
- Add optional REST foundation for future modules.
- Improve admin help extensibility for module-provided help sections.

LSOS:

- Build CRM MVP with contacts, leads, pipeline stages, and notes.
- Add basic roles and capabilities.
- Add activity history for important record changes.
- Add import/export planning for CRM records.

### v1.3

LID Core:

- Add integration contracts for LSOS modules.
- Add structured event hooks for reusable Core events.
- Expand test coverage for AJAX and progressive enhancement behavior.
- Improve release tooling and changelog automation.

LSOS:

- Add logistics and tracking MVP.
- Add automation rules MVP.
- Add notification routing.
- Add reporting dashboards for pipeline and operations.

### v2.0

LID Core:

- Consider namespace migration from `IDB\` to `LID\` only if the framework is extracted from IranianDubai-specific packaging.
- Formalize public interfaces for modules.
- Remove deprecated compatibility layers with migration documentation.
- Introduce stronger test and CI requirements before release.

LSOS:

- Release modular operating system baseline.
- Add AI-assisted workflows with reviewable suggestions.
- Add advanced permissions and audit trails.
- Add integration marketplace pattern for external systems.

## Future AI, CRM, Logistics, Tracking, and Automation Notes

AI modules:

- AI should assist, not silently decide.
- Store prompts, model outputs, and human approvals when they affect operations.
- Keep AI configuration outside Core.
- Avoid sending sensitive data to external providers without explicit controls.

CRM modules:

- Leads, contacts, companies, deals, and notes should have clear ownership.
- Every status change should be timestamped and attributable.
- CRM imports should validate and deduplicate before writing records.

Logistics modules:

- Shipments should use explicit state machines.
- Carrier integrations should be isolated behind adapters.
- Exceptions should be first-class records, not free-text-only notes.

Tracking modules:

- Tracking events should be append-only where practical.
- Public tracking views must not expose internal notes or private customer data.
- Event timestamps should store UTC and display in the site/user locale.

Automation modules:

- Automations should be observable and testable.
- Every rule should have a dry-run or preview path where possible.
- Failed actions should be retried safely or surfaced for human review.
- Automation should never bypass permissions or audit requirements.

## Guiding Principle

LID Core should remain the dependable foundation. LSOS should become the intelligent operating layer. The two systems should cooperate through stable contracts, not by sharing uncontrolled implementation details.
