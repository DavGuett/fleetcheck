# Fleet Manager — Copilot Instructions

## Project Overview

**Fleet Manager** is a fleet maintenance management system for small operations (up to 20 light vehicles).  
Stack: **Laravel 11 · Inertia.js · Vue 3 (Composition API) · Tailwind CSS · Vite**

---

## Architecture & Conventions

### Laravel (Backend)

- Follow **Laravel conventions strictly**: resourceful controllers, form requests, policies, observers.
- All business logic lives in `app/Services/` — never in controllers or models.
- Use **Form Requests** for all validation (`app/Http/Requests/`).
- Use **API Resources** (`app/Http/Resources/`) for all JSON responses via Inertia.
- Database interactions use **Eloquent only** — no raw DB queries unless absolutely necessary.
- Use **DB transactions** for any operation that writes to more than one table.
- Define model relationships, casts, and fillable in every model. Never use `$guarded = []`.
- Use **Eloquent Scopes** for reusable query filters (e.g., `scopeActive`, `scopeOverdue`).
- Schedule recurring checks (maintenance due, CNH expiry) via `app/Console/Kernel.php`.
- Dispatch **Jobs** for heavy processing; use **Laravel Notifications** for alerts.

#### File Structure (Backend)
```
app/
├── Console/
├── Events/
├── Http/
│   ├── Controllers/        # Thin controllers — delegate to Services
│   ├── Middleware/
│   ├── Requests/           # One FormRequest per action
│   └── Resources/          # API Resources for Inertia props
├── Models/                 # Eloquent models
├── Notifications/          # Laravel Notifications
├── Observers/              # Model Observers
├── Policies/               # Authorization policies
└── Services/               # All business logic
database/
├── factories/
├── migrations/
└── seeders/
routes/
├── web.php                 # All Inertia routes
└── auth.php
```

### Vue 3 / Inertia (Frontend)

- Use **Composition API** exclusively (`<script setup>`). Never use Options API.
- All pages live in `resources/js/Pages/` — mirroring the route structure.
- Reusable components go in `resources/js/Components/`.
- Shared layout in `resources/js/Layouts/AppLayout.vue`.
- Use `usePage()` from `@inertiajs/vue3` to access shared props (auth user, alerts count).
- Use `useForm()` from `@inertiajs/vue3` for all forms — never plain `axios`.
- Do not use Vuex or Pinia unless explicitly introduced. Prefer Inertia shared data and props.

#### File Structure (Frontend)
```
resources/js/
├── Components/
│   ├── Common/             # Buttons, Badges, Inputs, Modals, Tables
│   ├── Vehicles/
│   ├── Drivers/
│   ├── ServiceOrders/
│   ├── Fuel/
│   └── Dashboard/
├── Layouts/
│   └── AppLayout.vue       # Sidebar + Header shell
├── Pages/
│   ├── Auth/
│   ├── Dashboard/
│   ├── Vehicles/
│   ├── Drivers/
│   ├── MaintenancePlans/
│   ├── ServiceOrders/
│   ├── Fuel/
│   └── Alerts/
└── app.js
```

---

## Domain & Data Model

### Entities

| Model | Key Fields | Notes |
|---|---|---|
| `User` | `name, email, password, role` | Roles: `admin, gestor, motorista, financeiro` |
| `Vehicle` | `plate, brand, model, year, status, current_mileage` | Statuses: `ativo, em_manutencao, inativo` |
| `Driver` | `cnh_number, cnh_category, cnh_expiry, user_id, vehicle_id` | 1:1 with User; 1:N with Vehicles over time |
| `MaintenancePlan` | `type, trigger_type, trigger_km, trigger_days, next_due_at, vehicle_id` | Types: `preventiva, preditiva` |
| `ServiceOrder` | `type, status, opened_at, closed_at, total_cost, vehicle_id, user_id` | Statuses: `aberta, em_andamento, concluida` |
| `ServiceOrderItem` | `description, quantity, unit_cost, service_order_id` | — |
| `FuelRecord` | `fueled_at, liters, price_per_liter, mileage_at_fueling, vehicle_id, driver_id` | — |
| `Alert` | `type, status, message, triggered_at, alertable_type, alertable_id` | Polymorphic |
| `MileageLog` | `mileage, recorded_at, vehicle_id, user_id` | Append-only log |

### Key Business Rules

- A `Vehicle` can only have **one active `Driver`** at a time.
- When a `ServiceOrder` is closed, `total_cost` must be recalculated from all `ServiceOrderItems`.
- `MaintenancePlan.next_due_at` is recalculated automatically after each OS closure (Observer).
- `FuelRecord` entries trigger a recalculation of the vehicle's average consumption.
- `Alert` records are **polymorphic** — they can belong to `Vehicle`, `Driver`, or `MaintenancePlan`.
- Never delete records — use soft deletes (`SoftDeletes` trait) on all main entities.

---

## Authorization

Use **Laravel Policies** for every model. Use **Gates** for cross-model actions.

| Action | `gestor` | `motorista` | `financeiro` |
|---|---|---|---|
| Cadastrar/editar veículos | ✅ | ❌ | ❌ |
| Abrir OS corretiva | ✅ | ✅ | ❌ |
| Aprovar / fechar OS | ✅ | ❌ | ❌ |
| Registrar abastecimento | ✅ | ✅ | ❌ |
| Ver custos e relatórios | ✅ | ❌ | ✅ |
| Gerenciar planos de manutenção | ✅ | ❌ | ❌ |
| Ver alertas | ✅ | ✅ | ✅ |

Always authorize in the **controller** using `$this->authorize()` or policy middleware. Never check roles in the view.

---

## Design System

### Color Palette (CSS Variables)

```css
:root {
  --color-navy:    #1B2A3B;  /* Sidebar, header */
  --color-azure:   #2C6FBF;  /* Primary — buttons, links */
  --color-sky:     #4B9CE8;  /* Hover states */
  --color-ice:     #E8F2FC;  /* Info backgrounds */
  --color-mint:    #27A06B;  /* Success / active */
  --color-amber:   #E8903A;  /* Warning / alert */
  --color-crimson: #D94040;  /* Error / critical */
  --color-frost:   #F4F5F7;  /* General app background */
}
```

Always use these variables (or their Tailwind config equivalents). Never hardcode hex colors in components.

### Typography

Font: **Inter** (Google Fonts). Always loaded globally.

| Use | Size | Weight |
|---|---|---|
| Page title | 28px | 500 |
| Section title | 18px | 500 |
| Label / subtitle | 13px | 500 + uppercase |
| Body text | 14px | 400 |
| Auxiliary text | 12px | 400 |
| KPI number | 28px | 500 + `tabular-nums` |

### Status Badges

**Vehicles:**
| Status | Background | Text |
|---|---|---|
| `ativo` | `#EAF3DE` | `#3B6D11` |
| `em_manutencao` | `#FAEEDA` | `#854F0B` |
| `critico` | `#FCEBEB` | `#A32D2D` |
| `inativo` | `#F4F5F7` | `#5F5E5A` |

**Service Orders:**
| Status | Background | Text |
|---|---|---|
| `aberta` | `#E6F1FB` | `#185FA5` |
| `em_andamento` | `#FAEEDA` | `#854F0B` |
| `concluida` | `#EAF3DE` | `#3B6D11` |
| `vencida` | `#FCEBEB` | `#A32D2D` |

Create a reusable `<StatusBadge status="..." type="vehicle|os" />` component.

---

## Coding Standards

### PHP / Laravel

- PHP 8.2+ features allowed: `readonly` properties, enums, named arguments, match expressions.
- Use **PHP Enums** for `role`, `vehicle_status`, `os_status`, `alert_type`.
- Return `JsonResponse` never — use `Inertia::render()` for pages and `back()` / `redirect()` for mutations.
- Every controller method must have a **single responsibility**.
- Add `@throws` and return type hints to all Service methods.
- Write **PHPUnit tests** for all Services. Feature tests for all HTTP endpoints.

```php
// ✅ Good
public function store(StoreServiceOrderRequest $request): RedirectResponse
{
    $this->serviceOrderService->create($request->validated());
    return redirect()->route('service-orders.index');
}

// ❌ Bad — validation + logic in controller
public function store(Request $request)
{
    $request->validate([...]);
    ServiceOrder::create([...]);
}
```

### Vue / Inertia

- Always use `<script setup lang="ts">` (TypeScript preferred; JS accepted if types are too complex).
- Props must always be typed with `defineProps<{...}>()`.
- Emit events with `defineEmits<{...}>()`.
- Extract repeated logic into **composables** in `resources/js/Composables/`.
- Never call `axios` directly — use `useForm` or `router` from `@inertiajs/vue3`.

```vue
<!-- ✅ Good -->
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
const form = useForm({ plate: '', brand: '' })
const submit = () => form.post(route('vehicles.store'))
</script>

<!-- ❌ Bad -->
<script setup>
import axios from 'axios'
const submit = () => axios.post('/vehicles', { ... })
</script>
```

### Tailwind CSS

- Configure the Fleet Manager palette in `tailwind.config.js` under `theme.extend.colors`.
- Use semantic class names through Tailwind's config — avoid arbitrary values (`[#1B2A3B]`) for brand colors.
- Keep component styles co-located in `.vue` files. No separate CSS files unless for global utilities.

---

## Multi-Tenancy Preparation

The system is **single-tenant now** but must be easy to expand. Follow these rules:

1. **Do not add `company_id`** to any model yet.
2. **Do not create a `Company` model yet**.
3. Structure Services and Repositories so that a future **Global Scope** can be injected with minimal changes.
4. When writing queries, avoid joins that would break with tenant isolation.
5. Document any spot where `company_id` will eventually be needed with:
   ```php
   // TODO(multi-tenant): add ->where('company_id', auth()->user()->company_id) here
   ```

---

## Queue & Notifications

- All outbound notifications (`AlertNotification`, `MaintenanceDueNotification`) must be **queued** — never sent synchronously.
- Use the `database` notification channel by default; add `mail` as a second channel when the notification is critical.
- Alert generation logic lives in `app/Services/AlertService.php`.
- The scheduled command `app/Console/Commands/CheckMaintenanceDue.php` runs daily and calls `AlertService`.

---

## Testing Guidelines

- **Unit tests** → `tests/Unit/Services/` — test each Service method in isolation.
- **Feature tests** → `tests/Feature/` — one test file per controller, test HTTP status + DB state.
- Use **Factories** for all test data. Never seed production-style data in tests.
- Each test must `RefreshDatabase`.
- Test authorization: assert a `motorista` **cannot** access gestor-only routes (expect 403).

---

## Naming Conventions

| Artifact | Convention | Example |
|---|---|---|
| Model | PascalCase singular | `ServiceOrder` |
| Migration | snake_case descriptive | `create_service_orders_table` |
| Controller | PascalCase + `Controller` | `ServiceOrderController` |
| Service | PascalCase + `Service` | `ServiceOrderService` |
| Form Request | Action + Model + `Request` | `StoreServiceOrderRequest` |
| Policy | Model + `Policy` | `ServiceOrderPolicy` |
| Vue Page | PascalCase, inside route folder | `Pages/ServiceOrders/Index.vue` |
| Vue Component | PascalCase, descriptive | `ServiceOrderStatusBadge.vue` |
| Composable | `use` + PascalCase | `useVehicleStatus.ts` |
| Route name | dot.notation | `service-orders.store` |

---

## What to Avoid

- ❌ Logic in Blade/Vue templates beyond simple conditionals.
- ❌ Calling Eloquent directly in controllers — always go through a Service.
- ❌ Using `$request->all()` — always use `$request->validated()`.
- ❌ Hardcoded role strings (`'gestor'`) — use the `Role` enum.
- ❌ `dd()`, `dump()`, `var_dump()` left in committed code.
- ❌ Storing calculated values (e.g., `total_cost`) without a corresponding Service method to recalculate them.
- ❌ Using Options API in Vue components.
- ❌ Direct `axios` calls — use Inertia's `useForm` or `router`.
- ❌ Arbitrary Tailwind color values for brand colors — use the configured palette.
