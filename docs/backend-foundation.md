# Cibo Backend Foundation

## Folder Structure

- `includes/`
  Shared PHP code used by pages and APIs.
- `includes/db.php`
  Central MySQL connection file.
- `includes/repositories/`
  Future database query classes.
- `includes/services/`
  Future business-logic classes.
- `api/`
  User-side API endpoints.
- `admin/api/`
  Admin-side API endpoints.
- `database/`
  SQL files for schema, migrations, and seed data.
- `database/cibo_foundation.sql`
  Beginner-friendly starter schema for the core application tables.
- `database/migrations/`
  Future incremental schema changes.
- `database/seeds/`
  Future sample or starter data.
- `docs/`
  Project notes and setup guides.

## Development Direction

- MySQL should become the source of truth.
- PHP sessions should handle authentication state.
- `localStorage` should be reduced to temporary UI convenience only.
- Repositories should handle SQL access.
- Services should handle business logic.

## Database Setup Standard

- The canonical MySQL database name for the full project is `cibo_db`.
- The runtime PHP configuration in `includes/db.php` and the admin modules use `cibo_db`.
- For a fresh XAMPP/phpMyAdmin import, use `database/schema.sql`.
- `database/cibo_foundation.sql` is a simplified reference schema and is not the full application schema.

## Import Guidance

1. Create/import the database using `database/schema.sql`.
2. Ensure the imported database name remains `cibo_db`.
3. Use the default local runtime credentials from `includes/db.php` unless your XAMPP setup differs.

## Schema Mismatch Risk

- `database/schema.sql` is the source of truth for the current application.
- `database/cibo_foundation.sql` intentionally remains simpler and does not include the full restaurant/admin/catalog structure used by the live project.
- Importing `database/cibo_foundation.sql` alone can leave the admin panel and customer catalog flows incomplete.
