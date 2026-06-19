# 01 — Bootstrap + Config scaffolding

Status: ready-for-agent
Slug: 01-bootstrap-config
Depends: —
Estimate: 1h

## Goal

Scaffold project root: composer.json PSR-4 autoload, config with DB constants, bootstrap with autoload + session + error handler, .env.example template.

No PHP classes yet — just wiring infra.

## Files

- `composer.json` (create)
- `includes/config.php` (create)
- `includes/bootstrap.php` (create)
- `.env.example` (create)

## Acceptance

- [ ] composer.json autoloads `Silk\` namespace from `src/`
- [ ] composer.json autoloads `Silk\Includes\` from `includes/`
- [ ] config.php defines constants: DB_HOST, DB_NAME, DB_USER, DB_PASS, BASE_URL
- [ ] config.php reads from `.env` file (not .env.example)
- [ ] bootstrap.php calls session_start(), require config.php, require Composer autoload, set default timezone Asia/Jakarta, set error handler to Exception
- [ ] .env.example mirrors config.php keys with placeholder values
- [ ] .env.example contains APP_ENV=development

## Test

```bash
# verify autoload generates
composer dump-autoload
php -r "require 'includes/bootstrap.php'; echo 'OK\n';"
# expected: OK (no parse errors)
```

## Out of scope

- Database class
- Router
- Tailwind
