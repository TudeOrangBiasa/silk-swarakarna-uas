# 03 — Database schema + seed SQL

Status: ready-for-dev
Slug: 03-db-schema-sql
Depends: —
Estimate: 1h

## Goal

Write full SQL schema: 4 tables (pasien, dokter, layanan, pemeriksaan) with constraints, indexes, foreign keys, and seed data for demo.

## Files

- `database/silk_swarakarna.sql` (create)

## Acceptance

- [ ] Table pasien: id_pasien (VARCHAR PK = No Rekam Medis, format RM-XXX), nama_pasien, tanggal_lahir, no_hp, alamat, created_at, updated_at
- [ ] Table dokter: id_dokter (PK AI), nama_dokter, no_izin_praktik (UNIQUE), spesialisasi, no_hp, created_at, updated_at
- [ ] Table layanan: id_layanan (PK AI), nama_layanan, biaya (INT), created_at, updated_at
- [ ] Table pemeriksaan: id_periksa (VARCHAR PK = No Transaksi, format TRX-YYYYNNN), id_pasien (FK), id_dokter (FK), id_layanan (FK), tanggal_periksa (DATE), keluhan (TEXT), status_pemeriksaan (ENUM: Menunggu,Sedang Diperiksa,Selesai), created_at, updated_at
- [ ] All FK: ON DELETE RESTRICT (prevent delete master if used in transaksi)
- [ ] Indexes on status_pemeriksaan, tanggal_periksa, nama_pasien, nama_dokter, nama_layanan
- [ ] Seed inserts: 3 pasien, 2 dokter (THT specialist), 3 layanan (Audiometri, OAE, BERA), 2 pemeriksaan
- [ ] Engine = InnoDB, charset = utf8mb4
- [ ] SQL runs without error

## Test

```sql
-- Import test
SOURCE database/silk_swarakarna.sql;
SELECT COUNT(*) FROM pasien; -- 3
SELECT COUNT(*) FROM dokter; -- 2
SELECT COUNT(*) FROM layanan; -- 3
SELECT COUNT(*) FROM pemeriksaan; -- 2
```

## Out of scope

- Migration versioning
- Stored procedures
- Triggers
