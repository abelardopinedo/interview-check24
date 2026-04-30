# Implementation Plan - Insurance Quote Backend (Check24)

This project is a Symfony-based backend service designed to aggregate insurance quotes from multiple providers. It features a modular architecture that allows for easy integration of new providers and robust request validation.

## Architecture Overview

The backend is built following clean architecture principles, emphasizing decoupling and extensibility.

### Core Components

1.  **Controller Layer**:
    *   `CalculateQuoteController`: Handles the main entry point for quote calculations.
    *   `ProviderMockController`: Provides mock endpoints for external providers (A and B) to simulate real-world API interactions.

2.  **Service Layer**:
    *   `ProviderSearchService`: The orchestrator that fetches quotes from all registered providers, normalizes the data, applies discounts, and sorts the results.
    *   `ProviderInterface`: A contract that all insurance providers must implement.
    *   `ProviderA`, `ProviderB`: Concrete implementations for specific external services.

3.  **Data Transfer Objects (DTOs)**:
    *   **External API DTOs**: `CalculateRequestDTO` validates the incoming request from the frontend.
    *   **Provider Request DTOs**: `ProviderARequestDTO`, `ProviderBRequestDTO` used to transform our internal data into the format required by each specific provider.
    *   **Provider Response DTOs**: `ProviderAResponseDTO`, `ProviderBResponseDTO` used to translate the raw responses received from external providers into the internal format required by our application.

## Key Design Decisions

### 1. Tagged Provider Strategy
We use Symfony's Dependency Injection container to tag all classes implementing `ProviderInterface` with `app.insurance_provider`. The `ProviderSearchService` then receives a `tagged_iterator`, allowing us to add new providers simply by creating a new class and implementing the interface, without modifying the orchestrator.

### 2. Asynchronous Parallel Request Handling
The `ProviderSearchService` is designed to fetch quotes from all providers in parallel. By leveraging asynchronous non-blocking I/O (likely Symfony's `HttpClient`), the system initiates all requests simultaneously and then resolves them, significantly reducing the total response time for the user.

### 3. Unified Validation & Separation of Concerns
Request validation is centralized in the `CalculateRequestDTO` (using Symfony Constraints) specifically for the `/calculate` entry point. This ensures that only valid data reaches the service layer. 

Crucially, we maintain a strict separation between the request we receive and the requests we send to providers. Each provider has its own **Request DTO**, allowing us to map and validate data according to their specific requirements (e.g., field names, formats) before the external call is made.

### 4. Normalization and Scalability
We adapt each provider's data format using dedicated DTOs for both requests and responses. This ensures that the raw data from external providers is translated into a consistent internal format. This approach is designed for scalability; in the future, if a provider requires 60+ fields or returns complex structures, we can manage the translation logic within its specific DTO and Provider implementation without cluttering the core business logic. 

The `ProviderSearchService` applies a global "Campaign Discount" (5%) to providers enrolled in the campaign and sorts the final results based on the **effective price** (the lower value between the original and discounted price).

## Proposed Components & Files

### [Controller]
#### [CalculateQuoteController.php]
*   Exposes `POST /calculate`.
*   Uses `#[MapRequestPayload]` for automatic DTO conversion and validation.
*   Returns a sorted JSON list of quotes.

### [Service]
#### [ProviderSearchService.php]
*   Main logic for iterating over providers.
*   Handles error states gracefully (if one provider fails, others still return results).
*   Applies sorting (cheapest first).

#### [ProviderInterface.php]
*   Methods: `getQuote(CalculateRequestDTO $dto)`, `parseResponse($response)`, `hasCampaignDiscount()`.

### [DTO]
#### [CalculateRequestDTO.php]
*   Entry point DTO for the `/calculate` endpoint.
*   Includes validation constraints and logic to calculate the driver's age.

#### [ProviderARequestDTO.php] & [ProviderBRequestDTO.php]
*   Used to transform and validate data specifically for each provider's API requirements.
*   Enables scalability for complex provider integrations.

#### [ProviderAResponseDTO.php] & [ProviderBResponseDTO.php]
*   Used to translate and normalize the raw response data from external APIs into our application's unified quote format.

## Verification Plan

### Automated Tests (PHPUnit)
We will implement a comprehensive test suite covering the following critical areas:

1.  **Provider Price Logic**:
    *   Verify the internal calculation logic for each provider mock (Age brackets, Car types, Car usage multipliers).
    *   Test boundary cases (e.g., age exactly at the threshold of a new price bracket).

2.  **Robust Error Handling in `/calculate`**:
    *   **Provider 422 Errors**: Ensure the orchestrator gracefully handles missing or invalid fields from a provider response without failing the entire request.
    *   **Provider 500 Errors**: Ensure that internal server errors from one provider are caught and the user still receives quotes from other healthy providers.

3.  **Campaign Discount Validation**:
    *   Verify that the 5% discount is correctly applied only to providers enrolled in the campaign.

4.  **Final Quote Sorting**:
    *   Ensure the final list of quotes returned to the frontend is sorted in **ascending order** based on the effective price (price after discount).

### Manual Verification
*   **OpenAPI Documentation**: Use the OpenAPI UI (provided by NelmioApiDocBundle) to manually trigger calculations and inspect JSON responses.
*   **E2E Integration**: Verify the full flow from the Vue.js frontend, ensuring the table updates correctly with sorted results from the backend.
