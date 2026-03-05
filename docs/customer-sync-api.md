# Customer Sync API - External Application Integration

## Overview

This API endpoint allows external applications to sync customer data with your CRM. It intelligently handles both new and existing customers based on the customer name.

## Endpoint

**URL:** `POST /api/customer/sync`

**Authentication:** Required (Bearer Token)

**Content-Type:** `application/json`

## How It Works

The endpoint uses an **upsert** pattern:

-   ✅ **If customer exists** (matched by name): Updates only the `properties` field
-   ✅ **If customer doesn't exist**: Creates a new customer with all provided data

## Request Format

```json
{
    "name": "SumSum",
    "phone": "+9647731906162",
    "email": "john@example.com",
    "birth_date": "1990-01-01",
    "address": "Iraq",
    "properties": {
        "total_sales": "500$",
        "total_bills": "5",
        "total_profit": "100$",
        "total_debit": "400$"
    }
}
```

### Request Fields

| Field        | Type   | Required | Description                                          |
| ------------ | ------ | -------- | ---------------------------------------------------- |
| `name`       | string | ✅ Yes   | Customer name (used for matching existing customers) |
| `phone`      | string | ❌ No    | Customer phone number                                |
| `email`      | string | ❌ No    | Customer email address                               |
| `birth_date` | date   | ❌ No    | Customer birth date (format: YYYY-MM-DD)             |
| `address`    | string | ❌ No    | Customer address                                     |
| `properties` | object | ✅ Yes   | Custom properties as JSON object                     |

## Response Format

### Success Response - Customer Updated (200)

```json
{
    "status": true,
    "data": {
        "message": "Customer properties updated successfully",
        "action": "updated",
        "customer": {
            "id": 123,
            "name": "SumSum",
            "phone1": "+9647731906162",
            "email": "john@example.com",
            "birthdate": "1990-01-01",
            "address": "Iraq",
            "properties": {
                "total_sales": "500$",
                "total_bills": "5",
                "total_profit": "100$",
                "total_debit": "400$"
            }
            // ... other customer fields
        }
    }
}
```

### Success Response - Customer Created (200)

```json
{
    "status": true,
    "data": {
        "message": "Customer created successfully",
        "action": "created",
        "customer": {
            "id": 124,
            "name": "SumSum",
            "phone1": "+9647731906162",
            "email": "john@example.com",
            "birthdate": "1990-01-01",
            "address": "Iraq",
            "properties": {
                "total_sales": "500$",
                "total_bills": "5",
                "total_profit": "100$",
                "total_debit": "400$"
            }
            // ... other customer fields
        }
    }
}
```

### Error Response - Validation Failed (422)

```json
{
    "message": "The name field is required.",
    "errors": {
        "name": ["The name field is required."],
        "properties": ["The properties field is required."]
    }
}
```

### Error Response - Server Error (500)

```json
{
    "status": false,
    "message": "Save unsuccessful"
}
```

## Usage Examples

### Example 1: Using cURL

```bash
curl -X POST "http://your-domain.com/api/customer/sync" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "SumSum",
    "phone": "+9647731906162",
    "email": "john@example.com",
    "birth_date": "1990-01-01",
    "address": "Iraq",
    "properties": {
        "total_sales": "500$",
        "total_bills": "5",
        "total_profit": "100$",
        "total_debit": "400$"
    }
  }'
```

### Example 2: Using PHP (Laravel HTTP Client)

```php
use Illuminate\Support\Facades\Http;

$customerData = [
    'name' => 'SumSum',
    'phone' => '+9647731906162',
    'email' => 'john@example.com',
    'birth_date' => '1990-01-01',
    'address' => 'Iraq',
    'properties' => [
        'total_sales' => '500$',
        'total_bills' => '5',
        'total_profit' => '100$',
        'total_debit' => '400$'
    ]
];

$response = Http::withToken($token)
    ->post('http://your-domain.com/api/customer/sync', $customerData);

if ($response->successful()) {
    $data = $response->json();
    $action = $data['data']['action']; // 'created' or 'updated'
    $customer = $data['data']['customer'];

    echo "Customer {$action}: {$customer['name']}";
}
```

### Example 3: Using JavaScript (Fetch API)

```javascript
const customerData = {
    name: "SumSum",
    phone: "+9647731906162",
    email: "john@example.com",
    birth_date: "1990-01-01",
    address: "Iraq",
    properties: {
        total_sales: "500$",
        total_bills: "5",
        total_profit: "100$",
        total_debit: "400$",
    },
};

fetch("http://your-domain.com/api/customer/sync", {
    method: "POST",
    headers: {
        Authorization: "Bearer YOUR_TOKEN_HERE",
        "Content-Type": "application/json",
    },
    body: JSON.stringify(customerData),
})
    .then((response) => response.json())
    .then((data) => {
        console.log(`Customer ${data.data.action}:`, data.data.customer);
    })
    .catch((error) => console.error("Error:", error));
```

### Example 4: Using Python (Requests)

```python
import requests

url = "http://your-domain.com/api/customer/sync"
headers = {
    "Authorization": "Bearer YOUR_TOKEN_HERE",
    "Content-Type": "application/json"
}

customer_data = {
    "name": "SumSum",
    "phone": "+9647731906162",
    "email": "john@example.com",
    "birth_date": "1990-01-01",
    "address": "Iraq",
    "properties": {
        "total_sales": "500$",
        "total_bills": "5",
        "total_profit": "100$",
        "total_debit": "400$"
    }
}

response = requests.post(url, json=customer_data, headers=headers)

if response.status_code == 200:
    data = response.json()
    print(f"Customer {data['data']['action']}: {data['data']['customer']['name']}")
```

## Important Notes

### 1. Customer Matching

-   Customers are matched **by name only** (case-sensitive)
-   If you need case-insensitive matching, the controller can be modified

### 2. Update Behavior

-   When a customer exists, **only the `properties` field is updated**
-   Other fields (phone, email, address, birth_date) are **not updated** for existing customers
-   If you need to update other fields, use the regular update endpoint: `POST /api/customer/{id}`

### 3. Default Values for New Customers

When creating a new customer, the following default values are set:

-   `customer_type_id`: 1
-   `country_id`: 1
-   `residence_id`: 1
-   `priority_id`: 1
-   `background_id`: 1
-   `user_id`: 1

### 4. Customer Movement

A `CustomerMovement` record is automatically created for new customers with:

-   `sales`: 0
-   `profit`: 0
-   `number_of_bills`: 0
-   `debt`: 0
-   `note`: "Created from external sync"

### 5. Logging

All sync operations are logged:

-   ✅ Successful updates: `Customer properties updated`
-   ✅ Successful creations: `New customer created from external sync`
-   ❌ Errors: `Failed to sync customer data` (with full error trace)

## Testing

### Test Case 1: Create New Customer

```bash
# First sync - should create customer
curl -X POST "http://localhost/api/customer/sync" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Customer",
    "phone": "+1234567890",
    "properties": {"test": "value"}
  }'

# Expected: action = "created"
```

### Test Case 2: Update Existing Customer

```bash
# Second sync with same name - should update properties only
curl -X POST "http://localhost/api/customer/sync" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Customer",
    "phone": "+9999999999",
    "properties": {"test": "new_value", "additional": "data"}
  }'

# Expected: action = "updated"
# Note: phone number will NOT be updated
```

## Troubleshooting

### Issue: Customer not found but should exist

-   Check that the name matches exactly (case-sensitive)
-   Verify there are no extra spaces in the name

### Issue: Properties not updating

-   Ensure the `properties` field is a valid JSON object
-   Check the logs in `storage/logs/laravel.log`

### Issue: Authentication failed

-   Verify the Bearer token is valid
-   Check that the token has not expired
-   Ensure the `auth:sanctum` middleware is properly configured
