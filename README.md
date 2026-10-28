# DASURECO Field Operations

A Laravel application for coordinating field operations, work orders, assets, materials, and maintenance records.

## Getting started

1. Install PHP dependencies with `composer install`.
2. Create the local environment file with `Copy-Item .env.example .env`.
3. Generate the application key with `php artisan key:generate`.
4. Create the SQLite database file if it does not exist with `php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"`.
5. Run database migrations with `php artisan migrate`.
6. Create the first administrator with `php artisan app:create-admin "Administrator Name" admin@example.com`. The command securely prompts for a password and confirmation.
7. Start the application with `php artisan serve` and open `http://localhost:8000`.

## Supabase PostgreSQL

The application connects to Supabase through its PostgreSQL database connection; a Supabase client key is not needed for Laravel database access. Set `DB_CONNECTION=pgsql` in `.env` and copy the host, port, database, username, and password from the Supabase dashboard's connection details. Set `DB_SSLMODE=require` to use TLS. If the direct database host is not reachable from your network, use the session pooler connection details provided by Supabase.

Run `php artisan migrate --force` to create the application's tables in the configured database. The migrations create the users, assets, work orders, materials, registration requests, activity logs, sessions, cache, and queue tables.

## Account access

Team members can submit a request from **Request access** on the sign-in page. A request is not a user account and cannot sign in until an administrator approves it. New requests are reviewed at `/admin/registration-requests`; an administrator selects the account's role when approving. Rejecting a request never creates an account.

Available roles are Administrator, Operations / Engineering Staff, Supervisor / Dispatcher, and Field Personnel. Applicants cannot request the Administrator role. Administrative routes are protected by server-side role middleware.

Signed-in users can update their name and email or change their password through **Profile settings**. Account role and activation status remain administrator-managed.

## User administration and permissions

Administrators manage accounts at `/admin/users`: create an account, assign its role, edit its details, and deactivate or reactivate it. Each change is written to the activity log. Role defaults and per-account grants are managed at `/admin/roles`.

Permissions are enforced on the backend through the `permission` middleware, not only by hiding interface elements. Every role starts from the defaults in `App\Models\Permission::defaultsForRole()`; once an administrator saves a role configuration, the stored grants become authoritative and can be restored to the defaults at any time. Individual accounts can hold extra permissions on top of their role.

Run `php artisan db:seed` to load the permission catalogue and the default role grants into the database.

## Inventory and material usage

Operations staff register materials and maintain the quantity on hand, unit, and reorder level for each SKU at `/operations/dashboard`. Field personnel record the materials they consume against an in-progress work order, which deducts the stock and writes a usage history row. When a material falls to or below its reorder level, Operations and Administrators receive a low-inventory notification.

## Notifications

In-app notifications are delivered through the database channel and are listed at `/notifications`, with an unread counter in the header. Notifications are raised for work-order assignment, priority changes, submissions for review, returns for correction, approvals, completions, and low inventory levels. Each notification respects the recipient's role.

## Tests

Run all tests with `php artisan test`. Authentication and account-approval tests can be run with `php artisan test --filter=AuthenticationTest`.
