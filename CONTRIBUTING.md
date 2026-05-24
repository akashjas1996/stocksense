# Contributing to StockSense

Thanks for taking the time to contribute. This is a simple PHP/MySQL app — no build tools, no package manager, just clone and run.

## Local setup

Follow the [README setup steps](README.md#setup), then:

```bash
# Start PHP's built-in server pointed at the public folder
php -S localhost:8000 -t public/
```

Open `http://localhost:8000` in your browser.

## Ground rules

- **Never commit `config/config.php`** — it's gitignored for a reason. Credentials in commits will cause your PR to be closed immediately.
- **No hardcoded values** — DB host, passwords, domain names all belong in `config/config.php`.
- **Mobile first** — test on a real phone or browser devtools mobile view before submitting. The app is primarily used on iPhone.
- **No new dependencies without discussion** — open an issue first if you want to add a JS library or PHP package.
- **Keep it simple** — this is intentionally a no-framework PHP app. Avoid abstractions that don't earn their complexity.

## Making a change

1. Fork the repo and create a branch off `main`
2. Make your change
3. Test it — at minimum hit the page you changed and one page either side of it
4. Open a PR using the template

## DB schema changes

If your change requires a schema change:

1. Update `database/schema.sql` (the canonical source of truth)
2. Write a migration file `database/migrate_your_change.sql` for existing installs
3. Document both in your PR description

## Reporting a bug

Use the [bug report template](.github/ISSUE_TEMPLATE/bug_report.md). Include the page URL, what you expected, and what happened. A screenshot goes a long way.

## Questions

Open an issue — no question is too small.
