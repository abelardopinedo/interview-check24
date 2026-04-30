# Implementation Plan - Log Visualization API

This plan covers the creation of administrative endpoints to expose the logging data for visualization. This will allow monitoring of system performance and detailed debugging of provider interactions.

## Proposed Changes

### 1. Data Transfer Objects (DTOs)
To ensure clean and optimized API responses, I will create dedicated DTOs for the log data.

#### [NEW] [LogSummaryDTO.php](file:///c:/Servers/Interviews/Test/backend/src/DTO/LogSummaryDTO.php)
- Basic fields for the list view (`id`, `endpoint`, `statusCode`, `latency`, `createdAt`).

#### [NEW] [LogDetailDTO.php](file:///c:/Servers/Interviews/Test/backend/src/DTO/LogDetailDTO.php)
- Extended fields for the detail view, including payloads and a collection of `ProviderLogDTO` objects.

#### [NEW] [ProviderLogDTO.php](file:///c:/Servers/Interviews/Test/backend/src/DTO/ProviderLogDTO.php)
- Details about an individual provider call (`providerName`, `status`, `latency`, `httpCode`, `requestPayload`, `responsePayload`).

---

### 2. Controller Implementation

#### [NEW] [LogController.php](file:///c:/Servers/Interviews/Test/backend/src/Controller/Admin/LogController.php)
This controller will handle all log-related administrative requests.

**Endpoints:**
- `GET /api/admin/logs`: Returns a list of `LogSummaryDTO` entries. 
    - Supports pagination (`page`, `limit`).
    - Supports sorting by `createdAt` or `latency`.
- `GET /api/admin/logs/{id}`: Returns a `LogDetailDTO` for a specific request, including all its provider sub-calls.
- `GET /api/admin/logs/stats`: Returns aggregated performance data:
    - Average latency per provider.
    - Error rates per provider.
    - Total request count.

---

### 3. Repository Enhancements

#### [MODIFY] [ProviderRequestLogRepository.php](file:///c:/Servers/Interviews/Test/backend/src/Repository/ProviderRequestLogRepository.php)
- Add custom DQL/SQL methods to calculate performance statistics (averages and error counts) efficiently in the database.

---

## Verification Plan

### Automated Tests
- **Functional Tests**: Create `LogControllerTest.php` to verify:
    - The log list is correctly paginated.
    - The detail view returns all associated provider logs.
    - Statistics are calculated correctly based on seeded data.

### Manual Verification
- Use `curl` or a tool like Postman to hit the new `/api/admin/logs` and `/api/admin/logs/{id}` endpoints.
- Verify that JSON responses match the expected DTO structures.
