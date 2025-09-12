# API Documentation - Subdomain System

## Base URL
```
http://admin.car-management.local:8000/api/admin/subdomain
```

## Authentication
جميع الـ API endpoints تتطلب الوصول من الـ central domain فقط.

## Endpoints

### 1. Get Tenant by Subdomain
```http
GET /tenant/by-subdomain?subdomain=example
```

**Response:**
```json
{
    "success": true,
    "data": {
        "tenant": {
            "id": "uuid",
            "name": "Company Name",
            "email": "company@example.com",
            "status": "active",
            "subscription_plan": "premium"
        },
        "domains": [
            {
                "id": 1,
                "domain": "example.car-management.local",
                "tenant_id": "uuid"
            }
        ],
        "is_active": true,
        "has_valid_subscription": true
    }
}
```

### 2. Get Tenant by Domain
```http
GET /tenant/by-domain?domain=example.car-management.local
```

**Response:** Same as above

### 3. Generate Subdomain URL
```http
POST /generate-url
Content-Type: application/json

{
    "subdomain": "example",
    "path": "/dashboard"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "url": "http://example.car-management.local:8000/dashboard",
        "subdomain": "example",
        "path": "/dashboard"
    }
}
```

### 4. Validate Subdomain
```http
POST /validate
Content-Type: application/json

{
    "subdomain": "example"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "subdomain": "example",
        "is_valid": true
    }
}
```

### 5. Get All Tenant Domains
```http
GET /domains
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "domain": "example.car-management.local",
            "tenant_id": "uuid",
            "tenant": {
                "id": "uuid",
                "name": "Company Name",
                "status": "active"
            }
        }
    ]
}
```

### 6. Clear Tenant Cache
```http
POST /clear-cache
Content-Type: application/json

{
    "tenant_id": "uuid",
    "domain": "example.car-management.local"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Cache cleared successfully"
}
```

### 7. Clear All Tenant Cache
```http
POST /clear-all-cache
```

**Response:**
```json
{
    "success": true,
    "message": "All tenant cache cleared successfully"
}
```

## Error Responses

### 400 Bad Request
```json
{
    "success": false,
    "message": "Subdomain is required"
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Tenant not found"
}
```

## Usage Examples

### JavaScript/Fetch
```javascript
// Get tenant by subdomain
const response = await fetch('/api/admin/subdomain/tenant/by-subdomain?subdomain=example');
const data = await response.json();

if (data.success) {
    console.log('Tenant:', data.data.tenant);
} else {
    console.error('Error:', data.message);
}
```

### cURL
```bash
# Get tenant by subdomain
curl "http://admin.car-management.local:8000/api/admin/subdomain/tenant/by-subdomain?subdomain=example"

# Generate subdomain URL
curl -X POST "http://admin.car-management.local:8000/api/admin/subdomain/generate-url" \
     -H "Content-Type: application/json" \
     -d '{"subdomain": "example", "path": "/dashboard"}'

# Clear cache
curl -X POST "http://admin.car-management.local:8000/api/admin/subdomain/clear-cache" \
     -H "Content-Type: application/json" \
     -d '{"tenant_id": "uuid"}'
```

### PHP
```php
// Get tenant by subdomain
$response = file_get_contents('http://admin.car-management.local:8000/api/admin/subdomain/tenant/by-subdomain?subdomain=example');
$data = json_decode($response, true);

if ($data['success']) {
    $tenant = $data['data']['tenant'];
    echo "Tenant: " . $tenant['name'];
} else {
    echo "Error: " . $data['message'];
}
```

## Rate Limiting
جميع الـ API endpoints محمية بـ rate limiting حسب إعدادات Laravel.

## Caching
- البيانات محفوظة في الكاش لمدة ساعة واحدة
- يمكن مسح الكاش عبر الـ API endpoints
- الكاش يتم مسحه تلقائياً عند تحديث البيانات

## Security Notes
- جميع الـ endpoints محمية من الـ CSRF
- التحقق من صحة البيانات المدخلة
- الوصول مقيد للـ central domains فقط
