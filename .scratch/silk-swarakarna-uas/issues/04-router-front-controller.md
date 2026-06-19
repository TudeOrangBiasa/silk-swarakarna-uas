# 04 — Router + Front Controller

Status: ready-for-agent
Slug: 04-router-front-controller
Depends: 01
Estimate: 1.5h

## Goal

Build public/index.php as front controller + router. Parse query string param `page`, route to view files. public/.htaccess rewrites all requests to index.php.

Routes: `pasien`, `pasien.create`, `pasien.edit`, `pasien.delete`, `dokter.*`, `layanan.*`, `pemeriksaan.*`, `pemeriksaan.update_status`, default → dashboard.

## Files

- `public/index.php` (create)
- `public/.htaccess` (create)

## Acceptance

- [ ] .htaccess RewriteEngine On, RewriteCond %{REQUEST_FILENAME} !-f, RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
- [ ] index.php requires bootstrap.php, parses `$_GET['url']`, maps to `$page` variable
- [ ] Route table maps string keys to view paths: `pasien` → `views/pasien/index.php`, `pasien.create` → `views/pasien/create.php`, etc.
- [ ] POST routes detect method and call class methods before rendering
- [ ] Default route (no `page`) renders `views/dashboard.php`
- [ ] Unknown route shows 404 message
- [ ] Each page route requires header.php, page content, footer.php
- [ ] POST handler for each entity: instantiate class, call method, redirect on success, pass error on failure

## Test

```bash
# Start PHP built-in server
php -S localhost:8000 -t public/
# Visit http://localhost:8000/ → dashboard
# Visit http://localhost:8000/pasien → pasien list
```

```php
// Expected routing logic
$allowedRoutes = [
    '' => 'dashboard',
    'pasien' => 'pasien/index',
    'pasien.create' => 'pasien/create',
    'pasien.edit' => 'pasien/edit',
    'pasien.delete' => 'pasien/delete',
    'dokter' => 'dokter/index',
    // ... etc
];
```

## Out of scope

- Authentication middleware
- CSRF tokens
- Route parameter validation
