# Provider Management Controller

Create a new controller to manage insurance providers in the database.

## User Review Required

> [!IMPORTANT]
> The database column is confirmed as `has_discount`. I will use `#[SerializedName("has_discount")]` in the `UpdateProviderDTO` to ensure correct mapping from the API request to the entity's `$hasDiscount` field.
>
> I will use `/api/providers` as the base route for these operations.

## Proposed Changes

### Backend Component

#### [MODIFY] [ProviderRepository.php](file:///c:/Servers/Interviews/Test/backend/src/Repository/ProviderRepository.php)
- Add a `searchByName(string $query)` method to allow partial name matching.

#### [NEW] [UpdateProviderDTO.php](file:///c:/Servers/Interviews/Test/backend/src/DTO/UpdateProviderDTO.php)
- Create a DTO to handle partial updates of a provider.

#### [NEW] [ProviderController.php](file:///c:/Servers/Interviews/Test/backend/src/Controller/ProviderController.php)
- Implement `index()`: `GET /api/providers`
- Implement `show(int $id)`: `GET /api/providers/{id}`
- Implement `search(Request $request)`: `GET /api/providers/search?q=...`
- Implement `update(int $id, UpdateProviderDTO $dto)`: `PATCH /api/providers/{id}`

## Verification Plan

### Automated Tests
- Create `tests/Controller/ProviderControllerTest.php`.
- Test cases:
    - `testListProviders`: Assert 200 OK and JSON array.
    - `testGetProvider`: Assert 200 OK for valid ID, 404 for invalid.
    - `testSearchProvider`: Assert correct results for partial match.
    - `testUpdateProvider`: Assert 200 OK and data changed in DB.

### Manual Verification
- Use `curl` or a REST client to hit the endpoints and verify responses.
- Check the SQLite database to confirm updates are persisted.
