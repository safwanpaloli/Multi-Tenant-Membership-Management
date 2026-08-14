# Multi-Tenant Membership Management API

This is a robust, multi-tenant API built with Laravel for managing memberships, processing purchases, and handling payment webhooks across isolated tenant environments. 

## Features
- **Multi-Tenancy Architecture**: Strict data isolation ensuring admins and consumers can only interact with data scoped to their specific tenant.
- **Role-Based Access Control (RBAC)**: Distinct permissions for `Admin` and `Consumer` roles.
- **Membership Management**: Admins can create, update, and manage memberships (including free membership limits and pricing cycles).
- **Purchasing System**: Consumers can view and purchase memberships on monthly or yearly billing cycles.
- **Payment Webhooks**: Safe, idempotent webhook handling for mock external payment providers.
- **API Documentation**: Comprehensive documentation available in `API_DOCUMENTATION.md`.

---

## 🚀 Getting Started

### Prerequisites
- PHP >= 8.2
- Composer
- MySQL

### Installation

1. **Clone the repository & install dependencies**
   ```bash
   composer install
   ```

2. **Environment Setup**
   Copy the `.env.example` file to create your local `.env`.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Configuration**
   By default, the `.env.example` is configured for a MySQL database named `multi-tenant-membership`. 
   
   Ensure you have a MySQL instance running, create a database named `multi-tenant-membership`, and update your `.env` with your actual MySQL credentials if they differ from the defaults:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=multi-tenant-membership
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

4. **Run Migrations & Seeders**
   This step is crucial. The seeders will generate the demonstration tenants (Tenant A and Tenant B), along with their admins, consumers, and configured payment providers.
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Start the Development Server**
   ```bash
   php artisan serve
   ```

---

## 🧪 Testing the Evaluator Scenario

The application is specifically configured to test out-of-the-box using the provided database seeders.

**Seeded Test Accounts (password: `password`)**:
- **Tenant A Admin**: `admin@tenant-a.test`
- **Tenant A Consumer**: `consumer@tenant-a.test`
- **Tenant B Admin**: `admin@tenant-b.test`
- **Tenant B Consumer**: `consumer@tenant-b.test`

You can use these accounts to generate Sanctum tokens via the `/api/admin/login` and `/api/consumer/login` endpoints to evaluate the API. 

---

## 📚 API Documentation

A complete list of all endpoints, required headers, request payloads, and example responses can be found in the root directory file: **`API_DOCUMENTATION.md`**.

---

## ⚙️ Automated Tests

This project includes a comprehensive suite of automated tests verifying:
- Authentication & Authorization Enforcements
- Role-Based Access Control (RBAC) rules
- Multi-Tenant Data Isolation (Admins and Consumers)
- Purchase Logic (Free allocations vs Paid transactions)
- Webhook idempotency

To run the test suite, ensure your environment supports the SQLite PHP driver (as it runs in-memory tests), and execute:
```bash
php artisan test
```
