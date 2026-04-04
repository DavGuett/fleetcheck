# Fleetcheck — Copilot Instructions

## Project Snapshot

**Fleetcheck** is based on the Laravel Vue starter and is evolving into a fleet maintenance system.

Current stack in this repository:

- **PHP 8.3**
- **Laravel 13**
- **Inertia.js 3**
- **Vue 3 + TypeScript (`<script setup lang="ts">`)**
- **Tailwind CSS 4**
- **Laravel Fortify** (authentication)
- **Laravel Wayfinder** (`resources/js/routes`, `resources/js/actions`)
- **Pest 4** (testing)

---

## Core Conventions

### Backend (Laravel)

- Keep controllers thin; delegate non-trivial domain behavior to services.
- For new domain features, place business logic in `app/Services/`.
- Use **Form Requests** for validation in all write endpoints.
- Use **Eloquent** for persistence; avoid raw SQL unless clearly justified.
- Wrap multi-table writes in **DB transactions**.
- Authorize actions in controllers with policies/gates (`$this->authorize(...)`).
- Prefer API Resources (`app/Http/Resources/`) for data passed to Inertia pages.
- Never use `$request->all()` in write flows; use `$request->validated()`.

### Frontend (Vue + Inertia)

- Use Composition API only: `<script setup lang="ts">`.
- Keep frontend directories lowercase:
  - `resources/js/pages/`
  - `resources/js/components/`
  - `resources/js/layouts/`
  - `resources/js/composables/`
- Prefer typed props with `defineProps<{ ... }>()`.
- Use Inertia primitives (`Form`, `useForm`, `router`) instead of direct `axios`.
- Use Wayfinder route helpers from `resources/js/routes/` and `resources/js/actions/`.
- Follow page layout conventions from `resources/js/app.ts`:
  - `auth/*` pages use `AuthLayout`
  - `settings/*` pages compose `AppLayout + settings/Layout`
  - default pages use `AppLayout`

---

## Repository Structure (Current)

```text
app/
├── Actions/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Models/
└── Providers/

resources/js/
├── actions/
├── components/
├── composables/
├── layouts/
├── pages/
├── routes/
├── types/
└── wayfinder/

routes/
├── web.php
├── settings.php
└── console.php

tests/
├── Feature/
└── Unit/
```

Notes:

- Existing pages include `users/UserList`, `auth/Login`, and `settings/Appearance`.
- Inertia page discovery is configured for `resources/js/pages` in `config/inertia.php`.

---

## Fleet Domain Direction (Strict Rules for New Modules)

When adding modules like `Vehicle`, `Driver`, `MaintenancePlan`, `ServiceOrder`, `FuelRecord`, or `Alert`, use these rules:

1. **Controller + service split is required**
   - Controller handles HTTP concerns only.
   - Domain workflow lives in `app/Services/*Service.php`.
2. **Write validation is required**
   - Every write action must use a dedicated Form Request.
3. **Authorization is required**
   - Add a policy per model and call `$this->authorize(...)` in endpoints.
4. **Output shaping is required**
   - Use API Resources for collections/items sent to Inertia.
5. **Lifecycle automation is explicit**
   - Use observers/jobs/notifications where behavior is event-driven.
6. **Deletion strategy defaults to soft deletes**
   - Main domain entities should use soft deletes unless there is a strong reason not to.
7. **Use enums for stable status/role fields**
   - Add enums when statuses/roles are introduced to avoid string drift.

### Fleet Module Definition of Done (DoD)

A new domain module is considered complete when it includes:

- Model + migration + factory.
- Resourceful controller methods for required actions.
- Form Requests for create/update (and other writes when needed).
- Policy with controller authorization calls.
- Service class for non-trivial business workflow.
- API Resource(s) for page/API data shape.
- Inertia page(s) in `resources/js/pages/<module>/`.
- Wayfinder-backed frontend route usage (no hardcoded URLs).
- Pest coverage:
  - Feature tests for HTTP status, authorization, and Inertia component/props.
  - Unit tests for service-level business rules.

### Multi-tenant Preparation

The app is single-tenant for now. Do **not** add `company_id` yet.
When writing query points that will need tenant filtering later, annotate with:

```php
// TODO(multi-tenant): add tenant scope filter here
```

---

## Design System Guidelines

Keep design guidance consistent and token-driven.

### Foundation

- Tailwind v4 is configured through `resources/css/app.css` (`@theme inline` + CSS variables).
- Prefer semantic tokens such as `bg-background`, `text-foreground`, `border-border`, `text-muted-foreground`.
- Do not hardcode brand hex values in feature components.
- If a new color/token is needed, define it in the theme layer first, then consume via semantic classes.

#### Color Reference (for token definitions)

Use this palette when introducing or adjusting fleet-branded tokens in the theme layer (not directly in feature markup):

| Token intent | Reference color |
|---|---|
| Fleet navy (layout shell) | `#1B2A3B` |
| Fleet azure (primary action) | `#2C6FBF` |
| Fleet sky (hover/info accent) | `#4B9CE8` |
| Fleet mint (success/active) | `#27A06B` |
| Fleet amber (warning) | `#E8903A` |
| Fleet crimson (error/critical) | `#D94040` |
| Fleet frost (neutral background) | `#F4F5F7` |

Implementation rule: map these references to CSS variables/Tailwind semantic tokens first, then use semantic classes in components.

### UI Composition

- Prefer reusable primitives in `resources/js/components/ui/` before adding one-off markup.
- Keep module-specific shared components in `resources/js/components/` with PascalCase filenames.
- Keep visual logic out of pages when it can be extracted into reusable components.

### Typography and Spacing

- Use existing Tailwind scale utilities; avoid arbitrary pixel values unless required.
- Prefer semantic text classes (`text-sm`, `text-muted-foreground`, etc.) over inline style attributes.
- Keep spacing rhythm consistent using standard spacing scale (`p-4`, `gap-6`, etc.).

#### Typography Scale Reference

| Use | Suggested utility/value | Weight |
|---|---|---|
| Page title | `text-2xl` (or ~28px equivalent) | `font-medium` / `font-semibold` |
| Section title | `text-lg` (or ~18px equivalent) | `font-medium` |
| Card/inline heading | `text-base` | `font-medium` |
| Body text | `text-sm` | `font-normal` |
| Auxiliary/meta text | `text-xs` | `font-normal` |
| KPI number | `text-2xl` + `tabular-nums` | `font-medium` |

Prefer existing component patterns (for example, `Heading.vue`) before introducing new one-off scales.

### State and Feedback Patterns

- Use consistent status patterns via reusable badges (e.g., `StatusBadge.vue`) instead of repeated inline class maps.
- Use destructive/secondary/muted token variants for error/warning/neutral messaging.
- Preserve accessible contrast in both light and dark mode.

#### Status Badge Reference

When implementing `StatusBadge.vue` or module-specific status badges, keep mappings consistent and token-driven:

| Domain state | Preferred badge style |
|---|---|
| `active`, `completed`, `ok` | success-style token mapping (typically secondary/success token) |
| `scheduled`, `in_progress`, `due_soon` | warning-style token mapping |
| `overdue`, `failed`, `critical` | destructive token mapping |
| `inactive`, `draft`, `unknown` | outline/muted token mapping |

Current reusable primitive is `resources/js/components/ui/badge` (`default`, `secondary`, `destructive`, `outline`). Compose domain statuses from these variants or approved semantic extensions.

### Accessibility

- Inputs require visible labels.
- Interactive elements must include focus-visible styles.
- Do not rely on color alone to convey critical state.

---

## Testing Conventions

- Use **Pest** syntax for all new tests.
- Feature tests live in `tests/Feature/`; unit tests live in `tests/Unit/`.
- `RefreshDatabase` is already applied for `tests/Feature` in `tests/Pest.php`.
- Use factories for test setup.
- For Inertia responses, assert both status and component/props (`assertInertia`).

Example expectation style:

```php
$response->assertInertia(fn ($page) => $page
    ->component('users/UserList')
    ->has('users')
);
```

---

## Naming Conventions

| Artifact | Convention | Example |
|---|---|---|
| Model | PascalCase singular | `ServiceOrder` |
| Controller | PascalCase + `Controller` | `VehicleController` |
| Service | PascalCase + `Service` | `ServiceOrderService` |
| Form Request | Action + Model + `Request` | `StoreVehicleRequest` |
| Policy | Model + `Policy` | `VehiclePolicy` |
| Vue page component | Path under lowercase folder | `pages/users/UserList.vue` |
| Vue component | PascalCase file name | `StatusBadge.vue` |
| Composable | `use` + PascalCase | `useVehicleStatus.ts` |
| Route name | dot notation | `users.index` |

---

## What to Avoid

- Business logic directly in controllers or Vue templates.
- Direct `axios` usage for form flows that should use Inertia helpers.
- Hardcoded URL strings when Wayfinder helpers already exist.
- Returning ad-hoc prop shapes to pages instead of using resources/consistent structures.
- Leaving debug calls (`dd`, `dump`, `var_dump`) in committed code.
