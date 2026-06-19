## Agent skills

### Issue tracker

Local markdown under `.scratch/<feature>/`. See `docs/agents/issue-tracker.md`.

### Triage labels

Default English status strings written as a `Status:` line near the top of each issue file. See `docs/agents/triage-labels.md`.

### Domain docs

Single-context: one `CONTEXT.md` at the repo root, ADRs under `docs/adr/`. See `docs/agents/domain.md`.

### Design system

Single spec of record: [DESIGN.md](DESIGN.md). Read it before editing any view, layout, or CSS file. Bootstrap 5.3 + Bootstrap Icons via CDN. No Tailwind. Brand color navy `#1e40af`. Bahasa Indonesia for all UI strings.

## Runtime

This project uses DDEV for the dev environment. No baremetal PHP required.

- All PHP commands run via `ddev exec php ...` or `ddev exec vendor/bin/...`
- Composer: `ddev composer <args>`
- Database: `ddev mysql`, `ddev import-db --src=...`, `ddev mysql -e "DROP DATABASE ..."` for reset
- DB host inside container: `db`. DB name: `silk_swarakarna`. User/pass: `db`/`db`.
- `.env` is auto-injected by DDEV via `.ddev/config.yaml` → `web_environment`. No manual edit needed.
- Verify PHP syntax: `ddev exec php -l <file>`
- Verify view renders: `ddev launch` then open `https://<project>.ddev.site/<route>`
