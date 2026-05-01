# Implementation Plan - Request Logging System

This plan covers the creation of two logging tables to track incoming API requests and outgoing provider requests. This will improve system observability and simplify debugging.

## Proposed Changes

### Database Schema

#### [NEW] [RequestLog.php](file:///c:/Servers/Interviews/Test/backend/src/Entity/RequestLog.php)
This entity will track all incoming requests to our API.

**Fields:**
- `id` (Primary Key)
- `request_payload` (JSON/Text)
- `response_payload` (JSON/Text)
- `latency` (Float/Integer - ms)
- `endpoint` (String)
- `http_method` (String)
- `status_code` (Integer)
- `created_at` (DateTimeImmutable)

#### [NEW] [ProviderRequestLog.php](file:///c:/Servers/Interviews/Test/backend/src/Entity/ProviderRequestLog.php)
This entity will track every individual call made to external providers.

**Fields:**
- `id` (Primary Key)
- `request_id` (ManyToOne -> RequestLog) - The parent request that triggered this provider call.
- `provider_id` (ManyToOne -> Provider) - Which provider was called.
- `request_payload` (JSON/Text)
- `response_payload` (JSON/Text)
- `latency` (Float/Integer - ms)
- `status` (String: completed, failed)
- `http_code` (Integer)
- `url` (String) - The exact URL called.
- `error_message` (Text) - Detailed error if status is 'failed'.
- `created_at` (DateTimeImmutable)

---

## Verification Plan

### Automated Tests
- I will verify the entities are correctly mapped by running:
  `php bin/console doctrine:schema:validate`
- I will generate the migration and verify its content:
  `php bin/console make:migration`

### Manual Verification
- Inspect the generated migration file to ensure all fields are present and types are correct.
