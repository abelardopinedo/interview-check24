# Insurance Comparison App: Frontend Implementation Plan

This plan outlines the architecture and logic for a Vue.js application that calculates car insurance quotes by interacting with a Symfony backend.

## 1. Project Architecture
The application will follow a modular component structure:
- **App.vue**: Root component managing global state (quotes, loading, errors).
- **QuoteForm.vue**: Handles user input, validation, and state persistence.
- **QuoteList.vue**: Displays results in a sortable, custom-built table.
- **Types**:
    - `RequestQuote.ts`: Interface for the search payload.
    - `Quote.ts`: Interface for the provider results.

## 2. Component Logic & Features

### 2.1 Input Form (`QuoteForm.vue`)
This component will manage the search parameters and enforce business rules.

**Fields & Validation Logic:**
- `driver_birthday`: Date input.
- `car_type`: Select dropdown with options: `Turismo`, `SUV`, `Compacto`.
- `car_use`: Radio buttons for `Privado` or `Comercial`.
- **Validation**: Fields are required. Specific business logic (like age limits) will be handled by the backend.
- **Submission**: On submit, a POST request to `/calculate` is triggered via the parent.

**State Persistence Logic:**
- **Storage**: Use `sessionStorage` to store the form state.
- **Behavior**: 
    - A `watch` (deep: true) will sync every change to `sessionStorage`.
    - On initialization, the component will attempt to hydrate its state from `sessionStorage`.
    - Since `sessionStorage` is used, the data persists on refresh but clears when the tab/window is closed.

### 2.2 Quote Results (`QuoteList.vue`)
A custom-built table to display and interact with quotes.

**Display Logic:**
- Columns: `Provider`, `Price`, `Final Price`, and `Discount`.
- **Campaign Rule**: If a `discount_price` exists, it represents the `Final Price`. The `Discount` column will explicitly show the applied campaign (e.g., "5%").
- **Empty State**: If no quotes are returned, display: *"No hay ofertas disponibles."*

**Sorting Logic:**
- Default: Sorted by **Final Price** ascending.
- Interaction: A toggle button/header will allow switching between `asc` and `desc`.
- Implementation: A `computed` property will return the sorted array based on the `Final Price`.

**Cheapest Highlight:**
- Logic: Identify the quote with the minimum **Final Price**.
- Visual: 
    - Add a **"Mejor Precio"** Badge.
    - Apply a subtle **green background** to the row.
    - If it has a discount, amplify the **discount field** (e.g., larger font, brighter color, or bold weight) in its specific column.

### 2.3 Global State & API (`App.vue`)
- **Loading State**: A boolean `isLoading` will be tracked. When `true`, the submit button in `QuoteForm` will be disabled.
- **Error Handling**: Use a `try/catch` block around the axios request. Map 4xx/5xx errors to user-friendly messages displayed in a global error banner.

## 3. Data Models (`src/types/`)

### `RequestQuote.ts`
```typescript
export interface RequestQuote {
  driver_birthday: string;
  car_type: string;
  car_use: string;
}
```

### `Quote.ts`
```typescript
export interface Quote {
  provider: string;
  price: number;
  discount_price?: number;
  currency: string;
}
```

## 4. Visual Design & User Experience
- **Aesthetics**: Implement a "Dark Mode" premium look using gradients and glassmorphism (backdrop-filter).
- **Typography**: Use "Inter" for high readability.
- **Animations**: Subtle fade-in for the results table and a smooth loading spinner.

## 5. Automated Testing Plan (TDD Approach)
The project will follow a **Test-Driven Development (TDD)** methodology.

- **Unit & Component Tests (`Vitest`)**:
    - **Empty State**: Verify that "No hay ofertas disponibles" appears when the quote list is empty.
    - **Form Emission**: Test that `QuoteForm` emits correctly formatted data.
    - **Sorting**: Test that sorting logic works correctly based on the **Final Price** for both `asc` and `desc` orders.
    - **Highlighting**: Verify that the "Mejor Precio" badge and green background are applied to the cheapest row.
    - **Campaign Visibility**: Ensure discounts are correctly calculated and amplified visually.

## 6. Implementation Steps (TDD Flow)
1.  **Phase 1**: Setup types and basic folder structure.
2.  **Phase 2**: **Write Automated Tests** for `QuoteForm.vue` and `QuoteList.vue`.
3.  **Phase 3**: Implement `App.vue` logic for API interaction and **backend validation error handling** (mapping violations to user-friendly messages).
4.  **Phase 4**: Implement `QuoteForm.vue` (persistence and basic field requirements) to pass tests.
5.  **Phase 5**: Implement `QuoteList.vue` (sorting, highlighting, and discount amplification) to pass tests.
6.  **Phase 6**: Final visual polish and responsive layout.
