# Customer Properties API - Implementation Guide

## Overview

I've added two new API endpoints to manage customer properties stored as JSON in the database.

## Database Schema

The `customers` table has a `properties` column defined as:

```php
$table->json('properties')->nullable();
```

## New API Endpoints

### 1. Update Customer Properties

**Endpoint:** `PUT /api/customer/{id}/properties`

**Purpose:** Receive JSON data from an external API and update the customer's properties field.

**Request Headers:**

```
Authorization: Bearer {your-token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "properties": {
        "custom_field_1": "value1",
        "custom_field_2": "value2",
        "nested_data": {
            "key": "value"
        },
        "array_data": [1, 2, 3]
    }
}
```

**Success Response (200):**

```json
{
    "status": true,
    "data": {
        "message": "Properties updated successfully",
        "customer": {
            "id": 1,
            "name": "Customer Name",
            "properties": {
                "custom_field_1": "value1",
                "custom_field_2": "value2"
            }
            // ... other customer fields
        }
    }
}
```

**Error Response (422):**

```json
{
    "message": "The properties field is required.",
    "errors": {
        "properties": ["The properties field is required."]
    }
}
```

---

### 2. Get Customer Properties

**Endpoint:** `GET /api/customer/{id}/properties`

**Purpose:** Retrieve the properties JSON data for a specific customer.

**Request Headers:**

```
Authorization: Bearer {your-token}
```

**Success Response (200):**

```json
{
    "status": true,
    "data": {
        "custom_field_1": "value1",
        "custom_field_2": "value2",
        "nested_data": {
            "key": "value"
        }
    }
}
```

**Error Response (404):**

```json
{
    "status": false,
    "message": "Load failed"
}
```

---

## Usage Examples

### Example 1: Update Properties from External API

```php
// In your code where you receive data from external API
$externalApiData = [
    'user_preferences' => ['theme' => 'dark', 'language' => 'en'],
    'custom_fields' => ['vip_status' => true, 'loyalty_points' => 1500],
    'metadata' => ['last_sync' => '2025-11-27']
];

// Make request to update customer properties
$response = Http::withToken($token)
    ->put("http://your-domain.com/api/customer/1/properties", [
        'properties' => $externalApiData
    ]);
```

### Example 2: Using cURL

```bash
curl -X PUT "http://your-domain.com/api/customer/1/properties" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "properties": {
      "subscription_type": "premium",
      "preferences": {
        "notifications": true,
        "newsletter": false
      },
      "tags": ["vip", "active"]
    }
  }'
```

### Example 3: Retrieve Properties

```bash
curl -X GET "http://your-domain.com/api/customer/1/properties" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Implementation Details

### Controller Methods

#### `updateProperties(Request $request, $id)`

-   **Validates** that `properties` field is present and is an array
-   **Finds** the customer by ID (throws 404 if not found)
-   **Updates** the properties field with the provided JSON data
-   **Logs** any errors that occur during the update
-   **Returns** success response with updated customer data

#### `getProperties($id)`

-   **Finds** the customer by ID (throws 404 if not found)
-   **Returns** the properties JSON data
-   **Logs** any errors that occur during retrieval

### Key Features

✅ **Validation:** Ensures properties is an array before saving  
✅ **Error Handling:** Comprehensive try-catch blocks with logging  
✅ **JSON Storage:** Automatically handles JSON encoding/decoding  
✅ **Flexible Structure:** Can store any valid JSON structure  
✅ **Authentication:** Protected by Sanctum middleware

---

## Notes

1. **JSON Structure:** The properties field can store any valid JSON structure (objects, arrays, nested data, etc.)

2. **Merging vs Replacing:** The current implementation **replaces** the entire properties object. If you need to **merge** new properties with existing ones, modify the `updateProperties` method:

```php
// To merge instead of replace:
$existingProperties = $customer->properties ?? [];
$newProperties = $request->input('properties');
$customer->properties = array_merge($existingProperties, $newProperties);
```

3. **Validation:** You can add custom validation rules for specific property fields if needed:

```php
$request->validate([
    'properties' => 'required|array',
    'properties.subscription_type' => 'sometimes|string|in:free,premium,enterprise',
    'properties.preferences' => 'sometimes|array'
]);
```

4. **Database:** The `properties` column is already set as `nullable()`, so it can be null if no properties are set.
