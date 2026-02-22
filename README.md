# Auth Gateway Microservice

A small Laravel-based microservice that validates JWTs and manages authentication-related concerns for a microservices ecosystem.

This repository implements a focused "auth gateway": it verifies incoming JSON Web Tokens (JWTs), enforces issuer and audience rules, issues and persists refresh tokens, and records authentication-related audit logs. The service is intended to be used as a central auth verification layer in front of internal APIs or as an authentication support service for other services.

Key features

- JWT verification using RS256 (RSA + SHA-256) with asymmetric keys.
- Configurable issuer and audiences (supports multiple audiences, OR logic).
- Refresh token persistence (database-backed) and audit logging of auth events.
- Clear separation of JWT parsing/validation code (see app/Services/Jwt).

Repository layout (important locations)

- app/Services/Jwt — JWT handling code, including `JwtVerifier` and `JwtKeys`.
- app/Models — Eloquent models: `User`, `RefreshToken`, `AuditLog`.
- config/auth_gateway.php — configuration for issuer, audiences and other gateway-specific settings.
- database/migrations — tables for users, refresh tokens, and audit logs.
- storage/jwt — RSA keypair used by the service (private.pem, public.pem).
- tests — automated tests (Pest/phpunit) for JWT handling and endpoints.

Getting started (developer)

These instructions assume you have PHP (8.x+ recommended), Composer, and SQLite or another database installed. Commands below are suitable for Windows cmd.exe.

1. Install dependencies

    composer install --no-interaction --prefer-dist

2. Create environment file

    copy .env.example .env

3. Set application key and environment values

    php artisan key:generate

4. Configure JWT keys and settings

- By default this project keeps keys under `storage/jwt/private.pem` and `storage/jwt/public.pem`. Ensure these files exist and are readable by the application. The app's `config/auth_gateway.php` and `config/filesystems.php` may be used to change locations.

- Required env settings (examples):

    JWT_PRIVATE_KEY=storage/jwt/private.pem
    JWT_PUBLIC_KEY=storage/jwt/public.pem
    JWT_ISSUER=https://auth.example.local
    JWT_AUDIENCES=service-a,service-b

Note: The application reads `auth_gateway.issuer` and `auth_gateway.audiences` from the config. If you prefer a single audience, set `JWT_AUDIENCE` / `JWT_AUDIENCES` in your env and adapt config accordingly.

Generating keys locally (example)

Run these OpenSSL commands (PowerShell/cmd compatible):

- Generate a 2048-bit RSA private key:

    openssl genpkey -algorithm RSA -out storage/jwt/private.pem -pkeyopt rsa_keygen_bits:2048

- Extract the public key:

    openssl rsa -pubout -in storage/jwt/private.pem -out storage/jwt/public.pem

Make sure the `storage/jwt` directory exists and your app can read the files.

Database setup

This project includes migrations for users, refresh tokens and audit logs. For a fast local setup using SQLite (already included in the repository as database/database.sqlite), run:

    php artisan migrate --seed

If you need to recreate the database during development:

    php artisan migrate:fresh --seed

Running the app (local)

Start the built-in PHP server (for development only):

    php artisan serve --host=127.0.0.1 --port=8000

Then call the API endpoints as documented in routes (see `routes/api.php`).

Testing

Unit and feature tests are available under `tests/`. Run the test suite with Pest (or phpunit):

    ./vendor/bin/pest

If you run into missing dependencies, ensure `composer install` completed successfully.

How JWT validation works (high-level)

The core JWT validation logic lives in `app/Services/Jwt/JwtVerifier.php`. Validation steps:

1. Parse the token.
2. Verify the signature using the configured RSA public key (RS256).
3. Assert `iss` (issuer) matches the configured issuer.
4. Validate token time claims (nbf/iat/exp) using a UTC clock.
5. Manually validate the `aud` (audience) claim: the verifier treats the configured audiences as an OR-list and accepts a token if any configured audience appears in the token's `aud` claim.

If any of the above checks fail, the verifier throws an exception; callers should handle the exception and return appropriate HTTP 401/403 responses.

Configuration reference

Check `config/auth_gateway.php` for the canonical configuration. Important keys:

- issuer — expected JWT issuer (string)
- audiences — array of allowed audiences
- refresh_token_ttl — refresh token time-to-live (if implemented)

Security notes

- Keep `private.pem` secret and out of source control. In production, use a secure secret store or environment variables rather than a repo file.
- Rotate keys carefully and provide a migration plan for active tokens (e.g. key identifiers or rolling keys).

Developer notes & contribution

- Tests live in `tests/`. Add unit tests for any new JWT validation behavior.
- Follow PSR-12 coding style used in the codebase. Use existing service and provider patterns when adding features.

Troubleshooting

- If tokens fail signature verification, ensure the public key used by the app matches the private key used to sign tokens.
- If audiences are rejected, check `auth_gateway.audiences` in `config/auth_gateway.php` and any env vars that populate it.
- Check `storage/logs/laravel.log` for detailed runtime errors.

Useful commands (Windows cmd.exe)

- Install deps: composer install
- Run migrations: php artisan migrate --seed
- Start server: php artisan serve --host=127.0.0.1 --port=8000
- Run tests: vendor\bin\pest

License

This project follows the licensing in its repository. Check the `LICENSE` file or composer.json for license metadata.

Contact / support

If you maintain this repo, list your contact or team process for issues, PRs and security disclosures here.
