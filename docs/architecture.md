# Application architecture

## HTTP routing

`bootstrap/app.php` registers the web, platform, console, and health routes and defines the web middleware stack. `routes/web.php` loads the web route files in registration order.

| Location | Responsibility |
| --- | --- |
| `routes/web/entry/` | Public pages, platform aliases, and feature routes with their own access rules |
| `routes/web/workspace/` | Workspace routes under the authenticated module-access group |
| `routes/web/account/` | Authenticated settings, self-service, HR, and support routes |
| `routes/auth.php`, `routes/super-admin.php` | Authentication and platform route groups |

Check both URI matching and named-route resolution before changing registration order. Existing aliases can share methods, names, or paths.

## Controllers and domain code

Controller classes in `app/Http/Controllers/` are the HTTP entry points. The attendance, dashboard, employee, payroll, and legacy platform controllers group related actions in controller-specific folders under `app/Http/Controllers/Concerns/`. The platform company controller has its own concerns under `app/Http/Controllers/SuperAdmin/Concerns/`.

Controller concerns contain HTTP actions and their request-specific helpers. Shared business operations belong in `app/Services/`. Eloquent models and relationships live in `app/Models/`. Tenant connection selection and request access checks live in `app/Http/Middleware/`.

Central and tenant schema changes have dedicated migration directories under `database/migrations/`. Use the connection and migration path for the data owner when adding a schema change.

## Views and assets

Blade views live in `resources/views/`. Keep feature-specific partials alongside the parent view. The admin workspace and employee directory keep their existing inline styles in local Blade partials, so their stylesheet order follows the parent views. Existing browser scripts and assets live under `public/`, with Vite entry points under `resources/js/` and `resources/css/`.

## Verifying structural changes

Compare the registered route methods, URIs, order, names, middleware, and actions before and after a routing change. Run the relevant scripts in `tests/regression/` with isolated fixtures for the affected features. PHPUnit discovers `tests/Unit/` and `tests/Feature/`; the standalone PHP and Node regression scripts are run separately.
