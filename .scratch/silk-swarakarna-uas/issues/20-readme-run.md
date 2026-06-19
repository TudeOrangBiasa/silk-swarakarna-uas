# 20 — README + .gitignore

Status: ready-for-dev
Slug: 20-readme-run
Depends: 01, 02, 03, 04, 05, 06, 07, 08, 09, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19
Estimate: 1h

## Goal

Write README.md with project description, prerequisites, setup steps (clone → composer install → .env → import SQL → run), and usage instructions. Write .gitignore excluding .env, vendor/, node_modules/, and OS files.

## Files

- `README.md` (create)
- `.gitignore` (create)

## Acceptance

- [ ] README starts with "# SILK-Swarakarna" heading and short description: "Sistem Informasi Layanan Klinik Swarakarna — klinik THT (Telinga Hidung Tenggorokan)"
- [ ] Prerequisites section: PHP 8.1+, MySQL 5.7+, Composer
- [ ] Setup steps in order:
  1. Clone repository
  2. Copy .env.example to .env, fill DB credentials
  3. Run `composer install`
  4. Import `database/silk_swarakarna.sql` to MySQL
  5. Run `php -S localhost:8000 -t public/`
- [ ] Folder structure diagram (abbreviated, showing key dirs)
- [ ] Account / login note: no authentication in v1
- [ ] Tech stack list: PHP 8 OOP, MySQL, Tailwind CSS, PDO
- [ ] .gitignore excludes: .env, vendor/, node_modules/, *.log, .DS_Store, Thumbs.db, /.idea/, /.vscode/, public/assets/css/tailwind.css (if using build step)

## Test

```bash
# Verify gitignore works
git init 2>/dev/null; git add .; git status
# Should NOT show .env, vendor/, node_modules/
```

```bash
# Verify README is valid markdown
php -r "echo file_exists('README.md') ? 'OK' : 'MISSING';"
```

## Out of scope

- Docker setup
- Production deployment guide
- Authentication/authorization docs
