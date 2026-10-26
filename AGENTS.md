# AGENTS.md

Guidance for AI coding agents working in this repository.

## Project overview

ITools PHP skill assessment: a Laravel application for recording buildings and calculating progressive electricity tariffs.

- **Backend:** Laravel 13, PHP 8.3+, PHPStan/Larastan level 7, Pint, PHPUnit-style tests (Pest is installed but the current suite uses PHPUnit classes).
- **Frontend:** Inertia.js 3 + Vue 3 + TypeScript, Vite (`vite-plus`), Tailwind CSS 4, shadcn-vue/reka-ui, Wayfinder-generated route helpers.
- **Auth:** Laravel Fortify (login, 2FA, passkeys) and Sanctum for API tokens.
- **Database:** SQLite by default (`database/database.sqlite`); tests use in-memory SQLite.

## Setup

Requires PHP 8.3+, Composer, Node 22+, and npm.

```bash
composer setup   # install, copy .env, generate key, migrate, npm install, build
```

`composer setup` does not seed data. To load the sample tariffs and buildings:

```bash
php artisan db:seed
# or reset the database and seed it:
php artisan migrate:fresh --seed
```

`migrate:fresh` is destructive and is blocked in production.

Run the full dev stack (HTTP server, queue listener, logs, and Vite) with:

```bash
composer dev
```

## Checks

Run the narrowest relevant checks while iterating, and the full suite before finishing.

| Area                     | Command                                   |
| ------------------------ | ----------------------------------------- |
| All PHP checks and tests | `composer test`                           |
| PHP formatting (write)   | `composer lint`                           |
| PHP formatting (verify)  | `composer lint:check`                     |
| PHP static analysis      | `composer types:check`                    |
| PHP tests                | `php artisan test`                        |
| Single PHP test          | `php artisan test --filter=DashboardTest` |
| Frontend lint            | `npm run check`                           |
| Frontend lint (fix)      | `npm run check:fix`                       |
| Frontend type-check      | `npm run types:check`                     |
| Production build         | `npm run build`                           |

CI (`.github/workflows/tests.yml`) runs `composer setup` and then `composer ci:check` on PHP 8.5/Node 22. `composer ci:check` runs frontend lint, frontend type-check, and `composer test`.

## Backend layout

- `app/BuildingType.php`, `app/Month.php`, `app/State.php` — root-level backed enums in the `App\` namespace (not `App\Enums`). Keep new domain enums there and validate them with `Rule::enum(...)`.
- `app/Models` — Eloquent models. `Building` and `Customer` use `SoftDeletes`; `Tariff` does not.
- `app/Http/Controllers` — web and API controllers. `BuildingController` and `TariffController` serve both the Inertia pages and API JSON, branching on `$request->routeIs('api.*')`. `ReportController` serves the reports page and the `/api/reports` endpoints, sharing the same filters. API resource routes live in `routes/api.php`; web pages live in `routes/web.php`; auth and settings routes live in `routes/settings.php`.
- `app/Http/Requests` — Form Requests own validation. Cross-field or business validation (for example, tariff tier overlap) belongs in `after()` hooks, not in controllers.
- `app/Http/Resources` — JSON response shaping. Return resources from controller actions rather than raw models.
- `app/Service/TariffCalculator.php` — static progressive tariff calculation. Buildings get their `bill` from this when created.
- `app/Concerns` — shared Fortify validation rules.
- `database/migrations`, `database/seeders` — schema and sample data.

## Frontend layout

- `resources/js/pages` — Inertia pages. The file path maps to the Inertia component name: `pages/Dashboard.vue` → `Dashboard`, `pages/settings/Profile.vue` → `settings/Profile`.
- `resources/js/layouts` — layouts. The page-to-layout mapping is in `resources/js/app.ts`.
- `resources/js/components` — app components. `components/ui` contains shadcn-vue primitives; treat these as vendored and compose or extend them rather than editing them unless the change is intentional.
- `resources/js/composables`, `resources/js/lib`, `resources/js/types` — composables, helpers, and shared TypeScript types.
- Wayfinder generates `resources/js/actions`, `resources/js/routes`, and `resources/js/wayfinder` during Vite runs. These directories are gitignored; import helpers from `@/routes` and `@/actions` but do not edit or commit generated files.
- Use `<script setup lang="ts">`, the `@/` alias, and existing UI primitives where possible. Use `cn()` from `@/lib/utils` for conditional Tailwind classes.
- Shared Inertia props live in `app/Http/Middleware/HandleInertiaRequests.php`. Server flash toasts are handled by `resources/js/lib/flashToast.ts`.

## Conventions

- Format PHP with Pint (`laravel` preset). Use 4 spaces and LF line endings (`.editorconfig`).
- Keep PHPStan level 7 passing. Add return types and docblocks where they clarify non-obvious array or model shapes.
- Vue/TypeScript style: 4 spaces, single quotes, semicolons, and an 80-column print width. Tailwind classes are sorted by the formatter; run `npm run check:fix` after UI changes.
- Tests are class-based PHPUnit tests extending `Tests\TestCase` with `RefreshDatabase`; follow the existing tests in `tests/Feature`. Pest is installed but not used by the current suite.
- Use model factories and `route()` helpers in tests. Inertia page existence is enforced by `config/inertia.php`.
- Avoid adding dependencies unless clearly necessary. Match the existing stack (Inertia/Vue, shadcn-vue, no separate state library).

## Domain rules

- `BuildingType` and `State` use lowercase string enum values. `month` is stored as an integer from 1 to 12; `App\Month` converts to and from numbers.
- Tariff tiers are progressive per `building_type`: `min_kwh` and `max_kwh` are inclusive, and `max_kwh = null` means no upper bound. Tiers for the same building type must not overlap.
- `TariffCalculator::getBuildingBill()` orders the initial tier first, then by `min_kwh`, and charges each tier's rate for the kWh falling in that tier. Preserve this behavior and add tests when changing rates or tiers.
- Creating and updating a building calculates its `bill` from `type` and `usability`. Recalculate it whenever either changes.
- Tariff updates and deletes have TODOs to queue recalculation of affected buildings.

## Known gaps and cautions

- The dashboard, building, tariff, and report web pages are public by design. The API building, tariff, and report routes are also unauthenticated; add authorization before exposing them beyond this assessment.
- Do not commit `.env`, `vendor`, `node_modules`, build output, or Wayfinder-generated directories.
- `AGENTS.md` is tracked in this repository; keep it updated when commands or architecture change.
