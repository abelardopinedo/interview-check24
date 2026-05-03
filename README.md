Insurance Comparison Tool - Code Challenge
Thank you for reviewing this project. This is a full-stack insurance comparison application utilizing Symfony 8 for the backend and Vue.js 3 for the frontend, fully containerized with Docker.

🚀 Getting Started
The environment is managed via Docker. All commands should be run from the project root.

Prerequisites
Docker: v29.4.1+

System Versions Used: PHP 8.4.20 | Symfony 8.0.8 | Node v22.18.0

Installation & Launch
Bash
# Build and start the containers in detached mode from the root
docker compose up --build -d
📂 Project Structure
The project is organized into two main service directories:

/backend: The Symfony 8.0.8 application (API, Business Logic, and Admin Panel).

/frontend: The Vue.js 3 application (Comparison Tool UI).

docker-compose.yml: Located in the root to orchestrate both services.

🛠 Tech Stack
Backend: Symfony 8.0.8 | PHP 8.4.20 | Composer 2.9.7

Frontend: Vue.js 3.5.33 | Vite 6.0.6 | Vue Router 4.6.4

API Docs: Swagger / OpenApi via NelmioApiDocBundle

🌐 Accessing the Application
Once the containers are running, you can access the different parts of the system:

1. Insurance Comparison Tool (Client)
URL: http://localhost

Usage: Fill in the information and submit to compare real-time results from the insurance providers.

2. Admin Panel (Monitoring)
URL: http://localhost/admin

Credentials: admin : adm1n

Features:

Overview: Global performance of providers and recent activity. Click "details" on any log for deep-dive performance metrics.

Logs: Filter by status code, search by ID, or sort by latency to analyze system behavior.

3. API Documentation
URL: http://localhost:8000/api/doc

Auth: Use admin : adm1n in the Authorize section to test protected endpoints.

🏗 Implementation Details
Provider Performance: The system tracks and logs every provider call, allowing for the real-time latency analysis seen in the Admin Panel.

Modern Vue: Built with the Vue 3 Composition API for a reactive, high-performance frontend.

Latest Symfony: Utilizing the newest Symfony 8 features to ensure the codebase follows the most current PHP standards.

Hope you enjoy the experience as much as I enjoyed building it!

Cheers!