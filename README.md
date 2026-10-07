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

## Account access

Team members can submit a request from **Request access** on the sign-in page. A request is not a user account and cannot sign in until an administrator approves it. New requests are reviewed at `/admin/registration-requests`; an administrator selects the account's role when approving. Rejecting a request never creates an account.

Available roles are Administrator, Operations / Engineering Staff, Supervisor / Dispatcher, and Field Personnel. Applicants cannot request the Administrator role. Administrative routes are protected by server-side role middleware.

Signed-in users can update their name and email or change their password through **Profile settings**. Account role and activation status remain administrator-managed.

## Tests

Run all tests with `php artisan test`. Authentication and account-approval tests can be run with `php artisan test --filter=AuthenticationTest`.
