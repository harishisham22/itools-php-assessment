# ITools PHP Skill Assessment

A Laravel application for recording buildings and calculating progressive
electricity tariffs. The UI is built with Inertia.js + Vue, and the same
controllers expose a JSON API.

## Features

- **Buildings** — create, edit, and soft-delete buildings. The list supports
  filtering by type, state, and month, searching by customer name, sorting by
  type or usage, and pagination (10 per page).
- **Dashboard** — a public billing overview with total billed, building and
  tariff counts, usage, average bill, a 12-month chart, a type breakdown, and
  the five most recent buildings.
- **Tariffs** — full CRUD for progressive tariff tiers per building type, with
  validation that tiers do not overlap. The list can be filtered by building
  type and is paginated (10 per page).
- **Reports** — a public dashboard of bill totals with a 12-month bar chart,
  breakdowns by building type and state, summary cards (total billed, building
  count, usage, average bill), and filters for type, state, and month.
- **Automatic billing** — a building's `bill` is calculated from its `type` and
  `usability` whenever it is created or updated.
- **Authentication** — Laravel Fortify provides login, registration, password
  reset, two-factor authentication, and passkeys. Sanctum is available for API
  tokens.
- **Public pages** — the dashboard, building, tariff, and report pages are
  accessible without logging in.

## Tech stack

| Layer    | Tools                                                                |
| -------- | -------------------------------------------------------------------- |
| Backend  | Laravel 13, PHP 8.3+, PHPStan/Larastan level 7, Pint                 |
| Frontend | Inertia.js 3, Vue 3 + TypeScript, Tailwind CSS 4, shadcn-vue/reka-ui |
| Build    | Vite (`vite-plus`), Wayfinder-generated route helpers                |
| Database | SQLite by default; in-memory SQLite for tests                        |

## Requirements

- PHP 8.3+
- Composer
- Node.js 22+
- npm

## Setup

```bash
composer setup
```

This installs dependencies, copies `.env`, generates the app key, migrates the
database, installs npm packages, and builds the frontend.

`composer setup` does not seed data. To load the sample tariffs and buildings:

```bash
php artisan db:seed
```

To reset the database and seed it in one step (destructive; blocked in
production):

```bash
php artisan migrate:fresh --seed
```

Run the full development stack (HTTP server, queue listener, logs, and Vite)
with:

```bash
composer dev
```

## Routes

### Web

| Method    | URI                    | Description                   |
| --------- | ---------------------- | ----------------------------- |
| GET       | `/`                    | Welcome page                  |
| GET       | `/dashboard`           | Billing overview (public)     |
| GET       | `/buildings`           | Building list                 |
| GET       | `/buildings/create`    | Create building form          |
| POST      | `/buildings`           | Store building                |
| GET       | `/buildings/{id}/edit` | Edit building form            |
| PUT/PATCH | `/buildings/{id}`      | Update building               |
| DELETE    | `/buildings/{id}`      | Delete building (soft delete) |
| GET       | `/tariffs`             | Tariff list                   |
| GET       | `/tariffs/create`      | Create tariff form            |
| POST      | `/tariffs`             | Store tariff                  |
| GET       | `/tariffs/{id}/edit`   | Edit tariff form              |
| PUT/PATCH | `/tariffs/{id}`        | Update tariff                 |
| DELETE    | `/tariffs/{id}`        | Delete tariff                 |
| GET       | `/reports`             | Billing reports (public)      |
| GET       | `/login`, `/register`  | Authentication pages          |
| GET       | `/settings/profile`    | Profile settings (auth)       |
| GET       | `/settings/security`   | Password and 2FA (auth)       |
| GET       | `/settings/appearance` | Theme settings (auth)         |

### Building list query parameters

| Parameter   | Description                                           |
| ----------- | ----------------------------------------------------- |
| `type`      | `commercial` or `residential`                         |
| `state`     | State value, for example `selangor` or `kuala_lumpur` |
| `month`     | Month number from `1` to `12`                         |
| `search`    | Partial customer name match                           |
| `sort_by`   | `type` or `usability`                                 |
| `direction` | `asc` (default) or `desc`                             |
| `page`      | Page number                                           |

### Tariff list query parameters

| Parameter       | Description                   |
| --------------- | ----------------------------- |
| `building_type` | `commercial` or `residential` |
| `page`          | Page number                   |

### Report query parameters

| Parameter | Description                                           |
| --------- | ----------------------------------------------------- |
| `type`    | `commercial` or `residential`                         |
| `state`   | State value, for example `selangor` or `kuala_lumpur` |
| `month`   | Month number from `1` to `12`                         |

Invalid report filters are ignored rather than rejected.

## API

API routes live under `/api` and share the list filters above. Lists return
Laravel's standard paginated resource response (`data`, `links`, `meta`).

| Method    | URI                             | Description                  |
| --------- | ------------------------------- | ---------------------------- |
| GET       | `/api/buildings`                | Paginated buildings          |
| POST      | `/api/buildings`                | Create building              |
| GET       | `/api/buildings/{id}`           | Show building                |
| PUT/PATCH | `/api/buildings/{id}`           | Update building              |
| DELETE    | `/api/buildings/{id}`           | Delete building              |
| GET       | `/api/tariffs`                  | Paginated tariffs            |
| POST      | `/api/tariffs`                  | Create tariff                |
| GET       | `/api/tariffs/{id}`             | Show tariff                  |
| PUT/PATCH | `/api/tariffs/{id}`             | Update tariff                |
| DELETE    | `/api/tariffs/{id}`             | Delete tariff                |
| GET       | `/api/reports`                  | Full report payload          |
| GET       | `/api/reports/by-month`         | Bill totals by month         |
| GET       | `/api/reports/by-building-type` | Bill totals by building type |
| GET       | `/api/reports/by-state`         | Bill totals by state         |
| GET       | `/api/user`                     | Current user (Sanctum)       |

Report endpoints accept the same `type`, `state`, and `month` filters. The full
payload contains `summary`, `by_month` (all 12 months), `by_type`, `by_state`,
and `filters`. The grouped endpoints return `data` rows shaped as
`{ label, value, buildings, usability }`, where `value` is the total bill.

Creating a building ignores any `bill` sent by the client; the value is always
recalculated from `type` and `usability`.

Validation rules:

- **Building:** `customer_name` (required, ≤255 chars), `type` (enum),
  `month` (integer 1–12), `usability` (numeric), `state` (enum).
- **Tariff:** `building_type` (enum), `category` (required), `min_kwh`
  (integer ≥0), `max_kwh` (nullable, > `min_kwh`), `rate` (numeric ≥0),
  `is_initial_rate` (boolean). Tiers for the same building type must not
  overlap.

Example:

```bash
curl "http://localhost:8000/api/buildings?month=10&search=ahmad&page=1"
```

## Domain rules

- Tariff tiers are progressive per `building_type`. `min_kwh` and `max_kwh`
  are inclusive, and `max_kwh = null` means no upper bound.
- `TariffCalculator::getBuildingBill()` charges each tier's rate for the kWh
  that falls in that tier, ordering the initial rate first and then by
  `min_kwh`.
- `month` is stored as an integer from 1 to 12; the `App\Month` enum converts
  between names and numbers.

## Checks

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

CI runs `composer setup` followed by `composer ci:check`, which covers frontend
lint, frontend type-check, and the PHP test suite.

## Project structure

- `app/BuildingType.php`, `app/Month.php`, `app/State.php` — domain enums.
- `app/Http/Controllers` — Inertia pages and API JSON, branching on
  `$request->routeIs('api.*')`. `DashboardController` loads the overview and
  `ReportController` serves the reports page and its JSON endpoints.
- `app/Http/Requests` — validation, including tariff overlap checks.
- `app/Http/Resources` — API response shaping.
- `app/Service/TariffCalculator.php` — progressive bill calculation.
- `app/Service/ReportBuilder.php` — shared bill aggregation used by the
  dashboard and reports.
- `resources/js/pages` — Inertia pages (`buildings/Index.vue` →
  `buildings/Index`), including `reports/Index.vue`.
- `resources/js/components` — shared UI, including the pagination controls.
- `resources/js/types` — shared TypeScript types.
- `database/seeders` — sample tariffs and buildings.

Generated Wayfinder helpers live in `resources/js/actions`,
`resources/js/routes`, and `resources/js/wayfinder`. They are gitignored and
regenerated during Vite runs; do not edit them by hand.

## Known gaps

- The dashboard, building, tariff, and report web pages are public by design.
  The API routes are also unauthenticated; add authorization before exposing
  them beyond this assessment.
- Updating or deleting a tariff does not yet recalculate the bills of affected
  buildings (tracked as a TODO in the controller).
