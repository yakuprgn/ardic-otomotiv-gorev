# Ardıç Automotive Training Portal

## Project overview

This repository contains two independent Laravel applications. Personnel Service manages employees and publishes active personnel through a protected REST API. Training Portal consumes that API to define trainings, assign them to employees, and track completion. Training content, exams, and certificates are intentionally outside the requested mini-project scope.

## Architecture

- `personnel-service/`: employee CRUD UI, SQLite database, session-authenticated administration, Bearer-token REST API.
- `training-portal/`: training and assignment UI, separate SQLite database, server-side Personnel Service client.
- The applications never share a database. Training Portal stores an employee snapshot on each assignment so history remains readable if an employee is later removed from Personnel Service.
- `completed_at` is the single source of truth for completion status.

## Technology stack

PHP 8.3+, Laravel 13, SQLite, Blade, Bootstrap 5, Bootstrap Icons, SweetAlert2, PhpSpreadsheet, and Laravel HTTP Client.

## Requirements

- PHP 8.3 or newer with SQLite, PDO SQLite, mbstring, and cURL extensions
- Composer 2
- Two available local ports: 8001 and 8002

## Installation

Run these commands in both application directories:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create an empty `database/database.sqlite` file in each application. Do not point both applications at the same file.

## Environment configuration

Personnel Service:

```dotenv
DB_CONNECTION=sqlite
PERSONNEL_ADMIN_USERNAME=admin
PERSONNEL_ADMIN_PASSWORD_HASH=<password_hash output>
PERSONNEL_API_TOKEN=<long random token>
```

Generate the password hash with `php -r "echo password_hash('your-password', PASSWORD_DEFAULT), PHP_EOL;"`. Generate the API token with a cryptographically secure password manager or `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`.

Training Portal must use the same API token:

```dotenv
DB_CONNECTION=sqlite
PERSONNEL_SERVICE_URL=http://127.0.0.1:8001/api/v1
PERSONNEL_SERVICE_TOKEN=<same long random token>
PERSONNEL_SERVICE_TIMEOUT=5
TRAINING_ADMIN_USERNAME=admin
TRAINING_ADMIN_PASSWORD_HASH=<password_hash output>
```

Never commit either `.env` file.

## Database migrations

Run non-destructive migrations independently:

```bash
cd personnel-service && php artisan migrate
cd ../training-portal && php artisan migrate
```

Do not use `migrate:fresh` or `db:wipe` on an existing installation.

## Running both applications

In separate terminals:

```bash
cd personnel-service && php artisan serve --host=127.0.0.1 --port=8001
cd training-portal && php artisan serve --host=127.0.0.1 --port=8002
```

Open `http://127.0.0.1:8001/employees` and `http://127.0.0.1:8002`.

Both applications redirect unauthenticated browser requests to their own responsive login screen. Credentials continue to come from the application-specific environment variables shown above; passwords and API tokens are never rendered client-side.

## Management authentication

- Login forms are CSRF protected and rate limited to five attempts per minute.
- Credentials are checked server-side against the configured username and password hash.
- A successful login regenerates the session ID. Logout invalidates the session and regenerates the CSRF token.
- Protected HTML requests redirect to login when the session is missing or expired; AJAX requests receive HTTP 401 and the table manager redirects to login.
- Session lifetime and secure-cookie behavior are controlled through Laravel's standard `SESSION_LIFETIME`, `SESSION_SECURE_COOKIE`, and related session settings.

## Dynamic tables and XLSX exports

Personnel, training, and assignment lists use server-side filtering, sorting, and pagination. Inputs update after a 350 ms debounce, in-flight requests are cancelled with `AbortController`, and filter state is preserved in the URL so refresh and browser back/forward work. Without JavaScript the same forms and links continue to work as normal GET requests.

Each table can export the current page, every matching row, or selected rows. Exports are genuine Office Open XML `.xlsx` workbooks produced with PhpSpreadsheet (not renamed CSV), include styled headings and real Excel date values, and neutralize formula-leading cell content. Export routes use the same session authorization as management screens.

## API authentication and endpoints

Send `Authorization: Bearer <token>` to:

- `GET /api/v1/employees` - paginated active employees (`per_page` up to 100, optional `search`)
- `GET /api/v1/employees/{id}` - one active employee

Invalid or missing credentials return HTTP 401. Missing/soft-deleted employees return HTTP 404.

## Testing

In each application run:

```bash
php artisan route:list
php artisan migrate:status
php artisan test
php artisan view:cache
```

Tests use in-memory SQLite and `Http::fake()`; they do not alter either development database.

## Security approach

Both applications use CSRF protection, server-side validation, escaped Blade output, Eloquent parameter binding, throttling, session fixation protection, and secrets loaded only from environment variables. The API token remains server-side and is never rendered into HTML or JavaScript. Production deployments must use HTTPS and secure cookies.

## Design decisions

- Separate SQLite databases enforce the service boundary required by the assignment.
- Employee identity is verified live through the REST API before every assignment.
- Assignment snapshots preserve audit/history display without copying or synchronizing the personnel database.
- A database unique constraint on `(training_id, employee_id)` is the final defense against duplicate assignments, including concurrent requests.
- Trainings with assignment history cannot be deleted, preventing accidental cascade data loss.
- Personnel pagination is followed until `last_page`; the portal does not silently stop at the first page.
- Network failures produce a controlled warning while existing training history remains available.

## Demo flow

1. Open Personnel Service and create then edit an employee.
2. Call the employee API with its Bearer token.
3. Open Training Portal and create a training.
4. Assign it to the employee, mark it complete, and return to the dashboard.

## Known limitations

The environment-configured single-administrator approach does not provide user/role administration or password recovery. The UI depends on CDN-hosted Bootstrap, Bootstrap Icons, and SweetAlert assets and does not include training content, exams, certificates, queues, or deployment infrastructure.
