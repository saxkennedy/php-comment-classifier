# Sweetwater Comments Report

A small PHP application built for the Sweetwater web programmer test. It works
with a table of order comments and does two things:

1. **Comments report** (`public/index.php`) - a read-only web page that lists
   every comment, grouped under its best-match topic (candy, call me / don't
   call me, who referred me, signature on delivery, miscellaneous), tags each
   comment with every topic it matches, and offers category and ship-date
   filters.
2. **Ship-date fill** - parses the "Expected Ship Date" out of each comment and
   writes it into the `shipdate_expected` column. This runs automatically when
   the report loads and is also available as commands: `bin/backfill_shipdates.php` to fill and
   `bin/reset_shipdates.php` to revert to the original zeros for re-testing.

Built with plain PHP 8.3 and PDO. No frameworks, no Composer packages, and
vanilla JavaScript only (no libraries).

> Status: Complete.

The original test brief is preserved in [TEST_INSTRUCTIONS.md](TEST_INSTRUCTIONS.md).

## Run it with Docker (recommended)

One command brings up PHP and MySQL, imports the supplied data, and serves the
report. Nothing to configure.

```bash
docker compose up --build
```

Then open <http://localhost:8080>. The ship dates (task 2) fill in
automatically on that first load.

Optional dev commands once the stack is up:

```bash
docker compose exec app php bin/reset_shipdates.php     # revert to the original zeros
docker compose exec app php bin/backfill_shipdates.php  # fill the ship dates by hand
```

Stop everything with `docker compose down`.

## Run it locally (without Docker)

Requirements: PHP 8.1+ and a MySQL 8 server.

1. Create the database and import the supplied table. The dump stores empty
   ship dates as `0000-00-00`, which MySQL 8 rejects under strict mode, so the
   import relaxes `sql_mode`:

   ```bash
   mysql -u root -e "CREATE DATABASE IF NOT EXISTS sweetwater_test"
   mysql -u root --init-command="SET SESSION sql_mode=''" sweetwater_test < data/sweetwater_test.sql
   ```

2. Serve the report:

   ```bash
   php -S localhost:8000 -t public
   ```

   Then open <http://localhost:8000>. The ship dates (task 2) fill in
   automatically on load.

Optional dev commands:

```bash
php bin/reset_shipdates.php     # revert to the original zeros
php bin/backfill_shipdates.php  # fill the ship dates by hand
```

Connection settings default to a stock local MySQL (`127.0.0.1`, user `root`,
empty password, database `sweetwater_test`). If yours differ, copy
`.env.example` to `.env` and adjust.

## Running the tests

I've added a small dependency-free test suite.
 Run it locally:

```bash
php tests/run.php
```

Or inside the Docker container:

```bash
docker compose exec app php tests/run.php
```

It prints each result and exits non-zero if anything fails. The suite covers the ship-date parser and the comment classifier (common and edge cases for both.)


## How it works

- On load, `public/index.php` fills any outstanding ship dates from the comment
  text (via `ShipDate\ShipDateBackfiller`), then reads the table to render the
  report. 
- The supplied dump (`data/sweetwater_test.sql`) is never modified, so the
  original data is always recoverable and a fresh `docker compose up` restores
  it.  We do actually make an update, to meet the criteria of the instructions/business needs.
- `src/` holds the pieces: `Config` (env-based settings), `Database\Connection`
  (PDO), `Comments\*` (the category model, classifier, and repository), and
  `ShipDate\*` (the date parser and the backfiller).

## Assumptions/Decisions made (some with feedback from team)

- **Categorization is best-match.** A comment is placed under the single
  category it best fits (the one with the most keyword matches); ties fall back
  to a fixed priority order. Every category a comment matches is still shown as a tag, with
  the best match emphasized.
- **Ship dates are read from the explicit "Expected Ship Date:" label only**,
  formatted `M/D/YY` (US month/day/year). Other dates that appear in comment
  text (for example a "9/11/2018 memorial" reference) are intentionally ignored
  so they are not mistaken for ship dates.
- **Rows with no parseable ship date keep their original `0000-00-00`** and are
  shown as "Date Missing" in the report so they can be followed up, as they are exceptions.
- **The schema is left as provided.** `shipdate_expected` stays `NOT NULL`; the
  zero value is the indicator to the front to flag as exception.
