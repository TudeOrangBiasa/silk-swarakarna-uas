# 02 — Database PDO Singleton

Status: ready-for-agent
Slug: 02-database-class
Depends: 01
Estimate: 1h

## Goal

Build Database class with PDO singleton pattern. 1 connection shared app-wide. Methods: getInstance, query, execute, lastInsertId.

## Files

- `src/Database.php` (create)

## Acceptance

- [ ] Database::getInstance() returns PDO instance, creates on first call, reuses on subsequent
- [ ] Constructor private — no external instantiation
- [ ] __clone private — prevent clone
- [ ] query($sql, $params) returns array of associative rows
- [ ] execute($sql, $params) returns row count
- [ ] lastInsertId() returns last auto-increment ID
- [ ] PDO::ATTR_ERRMODE = ERRMODE_EXCEPTION
- [ ] PDO::ATTR_DEFAULT_FETCH_MODE = FETCH_ASSOC
- [ ] Read credentials from constants in config.php

## Test

```php
$db = Database::getInstance();
$db === Database::getInstance(); // true, singleton
$rows = $db->query("SELECT 1 AS test");
echo $rows[0]['test']; // 1
```

## Out of scope

- Multiple connections
- Query builder
- Migration runner
