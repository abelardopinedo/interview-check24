# Implementation Plan - JWT Authentication for Admin Dashboard

Implement a simple JWT-based authentication system for the admin area, including login/logout functionality.

## User Review Required

> [!IMPORTANT]
> This plan involves installing `lexik/jwt-authentication-bundle` and `symfony/security-bundle` on the backend. This is the standard, secure way to handle JWT in Symfony.
> I will configure a single admin user in the database or in-memory as per your "simple authentication" request.

## Proposed Changes

### Backend (Symfony)

#### [MODIFY] [composer.json](file:///c:/Servers/Interviews/Test/backend/composer.json)
- Add `symfony/security-bundle` and `lexik/jwt-authentication-bundle`.

#### [NEW] [User.php](file:///c:/Servers/Interviews/Test/backend/src/Entity/User.php)
- Create a `User` entity that implements `UserInterface` and `PasswordAuthenticatedUserInterface`.
- Map it to the existing `user` table (id, username, roles, password).

#### [NEW] [security.yaml](file:///c:/Servers/Interviews/Test/backend/config/packages/security.yaml)
- Configure the firewall to use JWT.
- **Scope**: Apply authentication only to `/admin` and `/providers` routes.
- **User Provider**: Use the `entity` provider pointing to the `User` entity.

#### [NEW] [lexik_jwt_authentication.yaml](file:///c:/Servers/Interviews/Test/backend/config/packages/lexik_jwt_authentication.yaml)
- Configure JWT settings using the existing `APP_SECRET` from `.env` to sign tokens (avoiding complex key management).

#### [MODIFY] [index.php](file:///c:/Servers/Interviews/Test/backend/public/index.php)
- No logic will be added here as Symfony's Security component handles routing and authentication via the Kernel. This ensures a clean and standard implementation.

> [!NOTE]
> **Secret Key**: A secret key (we'll use `APP_SECRET` from `.env`) is required to cryptographically sign the JWT. This prevents users from tampering with their tokens.

#### [NEW] [SecurityController.php](file:///c:/Servers/Interviews/Test/backend/src/Controller/SecurityController.php)
- Implement `/api/logout` (Backend acknowledgement for logout; token invalidation if needed, though JWT is typically stateless).

---

### Frontend (Vue.js)

#### [NEW] [LoginView.vue](file:///c:/Servers/Interviews/Test/frontend/src/views/admin/LoginView.vue)
- Create a premium, beautiful login page.

#### [NEW] [auth.ts](file:///c:/Servers/Interviews/Test/frontend/src/store/auth.ts)
- Create a simple auth store (using reactive state) to manage the JWT and login/logout state.

#### [MODIFY] [router/index.ts](file:///c:/Servers/Interviews/Test/frontend/src/router/index.ts)
- Add the `/admin/login` route.
- Add a navigation guard to protect `/admin` routes.

#### [MODIFY] [AdminLayout.vue](file:///c:/Servers/Interviews/Test/frontend/src/layouts/AdminLayout.vue)
- Add a logout button in the sidebar.
- **Action**: Delete the JWT cookie/token on the frontend upon logout.

#### [MODIFY] [DashboardView.vue](file:///c:/Servers/Interviews/Test/frontend/src/views/admin/DashboardView.vue)
- Add authentication check (though the router guard should handle most of it).

## Verification Plan

### Automated Tests
- I will verify the login flow using the browser subagent.
- I will check that protected routes return 401/403 when not logged in.

### Manual Verification
- Test login with valid/invalid credentials.
- Test logout and ensure the user is redirected to the login page.
- Ensure the JWT is stored (in a cookie or localStorage) and sent in subsequent requests.
