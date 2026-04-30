# Migrate Provider Configuration to SQLite

The goal is to move provider URLs and discount status from the `.env` file to a SQLite database.

## Proposed Changes

### Configuration

#### [MODIFY] [.env](file:///c:/Servers/Interviews/Test/backend/.env)
- Update `DATABASE_URL` to use SQLite.
- Keep the old provider env vars for now (to seed the DB) then they can be removed.

### Entity and Repository

#### [NEW] [Provider.php](file:///c:/Servers/Interviews/Test/backend/src/Entity/Provider.php)
- Create the `Provider` entity with `id`, `name`, `url`, and `hasDiscount` fields.

#### [NEW] [ProviderRepository.php](file:///c:/Servers/Interviews/Test/backend/src/Repository/ProviderRepository.php)
- Standard repository for the `Provider` entity.

### Database Migration and Seeding

#### [NEW] [Migration](file:///c:/Servers/Interviews/Test/backend/migrations/)
- Generate a migration to create the `provider` table.

#### [NEW] [SeedProvidersCommand.php](file:///c:/Servers/Interviews/Test/backend/src/Command/SeedProvidersCommand.php)
- A command to seed the initial data from the `.env` file into the database.

### Service Updates

#### [MODIFY] [ProviderA.php](file:///c:/Servers/Interviews/Test/backend/src/Service/Provider/ProviderA.php)
#### [MODIFY] [ProviderB.php](file:///c:/Servers/Interviews/Test/backend/src/Service/Provider/ProviderB.php)
- Update constructors to accept `ProviderRepository` instead of individual values.
- Fetch configuration from the database when needed.

#### [MODIFY] [services.yaml](file:///c:/Servers/Interviews/Test/backend/config/services.yaml)
- Remove hardcoded environment variable injections for `ProviderA` and `ProviderB`.

## Verification Plan

### Automated Tests
- Run existing tests to ensure the system still works after the migration.
- Create a new test to verify that data is correctly fetched from the database.

### Manual Verification
- Run `php bin/console app:seed-providers` and verify the SQLite database content.
- Check the API response to ensure quotes are still being fetched correctly.
