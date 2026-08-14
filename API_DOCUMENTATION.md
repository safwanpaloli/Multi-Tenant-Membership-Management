# Multi-Tenant Membership Management API Documentation

## Overview
This API allows for the management of multi-tenant memberships, including administration by tenant admins and consumption by users (consumers). The API uses Bearer token authentication (Sanctum).

Base URL path for all endpoints: `/api`

---

## Authentication

### Admin Login
- **Endpoint:** `POST /admin/login`
- **Description:** Authenticate a tenant administrator and return an API token.
- **Request Body:**
  ```json
  {
    "email": "admin@example.com",
    "password": "secretpassword"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "message": "Login successful.",
    "data": {
      "token": "1|token_string",
      "token_type": "Bearer",
      "user": {
        "id": 1,
        "name": "Admin Name",
        "email": "admin@example.com",
        "role": "admin",
        "tenant_id": 1
      }
    }
  }
  ```

### Consumer Register
- **Endpoint:** `POST /consumer/register`
- **Description:** Register a new consumer within the given tenant.
- **Request Body:**
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "secretpassword",
    "password_confirmation": "secretpassword",
    "tenant_slug": "tenant-slug"
  }
  ```
- **Response (201 Created):**
  ```json
  {
    "message": "Registration successful.",
    "data": {
      "token": "2|token_string",
      "token_type": "Bearer",
      "user": {
        "id": 2,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "consumer",
        "tenant_id": 1
      }
    }
  }
  ```

### Consumer Login
- **Endpoint:** `POST /consumer/login`
- **Description:** Authenticate a consumer and return an API token.
- **Request Body:**
  ```json
  {
    "email": "john@example.com",
    "password": "secretpassword"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "message": "Login successful.",
    "data": {
      "token": "3|token_string",
      "token_type": "Bearer",
      "user": {
        "id": 2,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "consumer",
        "tenant_id": 1
      }
    }
  }
  ```

### Logout
- **Endpoint:** `POST /logout`
- **Description:** Logout the currently authenticated user (Admin or Consumer) by revoking their current access token.
- **Headers:** 
  - `Authorization: Bearer {token}`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Logged out successfully."
  }
  ```

---

## Admin APIs

All Admin APIs require authentication (`Bearer Token`) and a valid admin role.

### List Memberships
- **Endpoint:** `GET /admin/memberships`
- **Description:** List memberships belonging to the authenticated admin's tenant.
- **Query Parameters:** `search`, `status`, `sort_by`, `sort_dir`, `per_page`
- **Response (200 OK):**
  ```json
  {
    "message": "Memberships retrieved successfully.",
    "data": {
      "data": [
         {
           "id": 1,
           "name": "Premium Plan",
           "status": "active",
           "price": "99.99"
         }
      ],
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
  ```

### Create Membership
- **Endpoint:** `POST /admin/memberships`
- **Description:** Create a membership for the authenticated admin's tenant.
- **Request Body:**
  ```json
  {
    "name": "Premium Plan",
    "description": "Full access plan.",
    "benefits": ["Benefit 1", "Benefit 2"],
    "price": 99.99,
    "monthly_price": 9.99,
    "yearly_price": 99.99,
    "free_membership_limit": 100,
    "status": "active"
  }
  ```
- **Response (201 Created):**
  ```json
  {
    "message": "Membership created successfully.",
    "data": {
      "membership": {
        "id": 1,
        "name": "Premium Plan",
        "description": "Full access plan.",
        "status": "active"
      }
    }
  }
  ```

### Get Membership
- **Endpoint:** `GET /admin/memberships/{id}`
- **Description:** Show a single membership scoped to the admin's tenant.
- **Response (200 OK):**
  ```json
  {
    "message": "Membership retrieved successfully.",
    "data": {
      "membership": {
        "id": 1,
        "name": "Premium Plan",
        "description": "Full access plan.",
        "status": "active"
      }
    }
  }
  ```

### Update Membership
- **Endpoint:** `PUT /admin/memberships/{id}`
- **Description:** Update a membership scoped to the admin's tenant.
- **Request Body:** Similar to create, but fields are optional.
  ```json
  {
    "name": "Updated Premium Plan"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "message": "Membership updated successfully.",
    "data": {
      "membership": {
        "id": 1,
        "name": "Updated Premium Plan",
        "status": "active"
      }
    }
  }
  ```

### Delete Membership
- **Endpoint:** `DELETE /admin/memberships/{id}`
- **Description:** Delete a membership scoped to the admin's tenant.
- **Response (200 OK):**
  ```json
  {
    "message": "Membership deleted successfully."
  }
  ```

### List Membership Purchases
- **Endpoint:** `GET /admin/membership-purchases`
- **Description:** List membership purchases for the admin's tenant.
- **Query Parameters:** `search`, `status`, `membership_id`, `date_from`, `date_to`, `per_page`
- **Response (200 OK):**
  ```json
  {
    "message": "Purchases retrieved successfully.",
    "data": {
      "data": [
         {
           "id": 1,
           "membership_id": 1,
           "consumer_id": 2,
           "amount": "9.99",
           "status": "completed"
         }
      ]
    }
  }
  ```

### Get Membership Purchase Details
- **Endpoint:** `GET /admin/membership-purchases/{id}`
- **Description:** Show details of a specific membership purchase within the admin's tenant.
- **Response (200 OK):**
  ```json
  {
    "message": "Purchase retrieved successfully.",
    "data": {
       "id": 1,
       "membership_id": 1,
       "consumer_id": 2,
       "amount": "9.99",
       "status": "completed",
       "membership": {
           "id": 1,
           "name": "Premium Plan"
       },
       "consumer": {
           "id": 2,
           "name": "John Doe",
           "email": "john@example.com"
       }
    }
  }
  ```

---

## Consumer APIs

All Consumer APIs require authentication (`Bearer Token`) using a consumer token.

### List Available Memberships
- **Endpoint:** `GET /consumer/memberships`
- **Description:** List available memberships for the consumer's tenant.
- **Response (200 OK):**
  ```json
  {
    "message": "Memberships retrieved successfully.",
    "data": [
      {
        "id": 1,
        "name": "Premium Plan",
        "description": "Full access plan.",
        "benefits": ["Benefit 1", "Benefit 2"],
        "price": 99.99
      }
    ]
  }
  ```

### Get Membership Details
- **Endpoint:** `GET /consumer/memberships/{id}`
- **Description:** Retrieve details of a specific membership available for the consumer's tenant.
- **Response (200 OK):**
  ```json
  {
    "message": "Membership retrieved successfully.",
    "data": {
      "id": 1,
      "name": "Premium Plan",
      "description": "Full access plan.",
      "benefits": ["Benefit 1", "Benefit 2"],
      "price": 99.99
    }
  }
  ```

### Purchase Membership
- **Endpoint:** `POST /consumer/memberships/{membership_id}/purchase`
- **Description:** Purchase a membership.
- **Request Body:**
  ```json
  {
    "billing_cycle": "monthly" // Can be "monthly" or "yearly"
  }
  ```
- **Response (201 Created):**
  ```json
  {
    "message": "Membership purchased successfully.",
    "data": {
      "id": 1,
      "membership_id": 1,
      "consumer_id": 2,
      "amount": "9.99",
      "status": "pending",
      "billing_cycle": "monthly"
    }
  }
  ```

### Get Purchase History
- **Endpoint:** `GET /consumer/me/memberships`
- **Description:** Get the consumer's purchase history.
- **Response (200 OK):**
  ```json
  {
    "message": "Purchase history retrieved successfully.",
    "data": [
      {
        "membership": {
          "id": 1,
          "name": "Premium Plan"
        },
        "billing_cycle": "monthly",
        "amount": 9.99,
        "status": "completed",
        "purchased_at": "2023-10-01T12:00:00+00:00"
      }
    ]
  }
  ```

---

## Payment Webhook

### Handle Payment
- **Endpoint:** `POST /webhooks/payments/{provider}`
- **Description:** Handle incoming webhooks from payment providers (e.g., Stripe, PayPal) to update payment statuses.
- **Headers:** 
  - `X-Signature: mock-signature` (Required for authentication validation)
- **Request Body:**
  ```json
  {
    "payment_id": "pay_123456",
    "status": "completed"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "message": "Webhook processed successfully."
  }
  ```
- **Error Response (400 Bad Request) - Invalid Payload:**
  ```json
  {
    "message": "Invalid payload"
  }
  ```
- **Error Response (401 Unauthorized) - Invalid Signature:**
  ```json
  {
    "message": "Invalid signature"
  }
  ```
- **Error Response (404 Not Found) - Purchase Not Found:**
  ```json
  {
    "message": "Purchase not found"
  }
  ```
