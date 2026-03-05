# NextHouse API — وثيقة الربط مع الـ Frontend

> **Base URL (محلي):** `http://localhost/NextHouse-Back/public/api`
> **Base URL (production):** استبدلها بـ Domain الفعلي

---

## 📌 الفهرس

1. [المصادقة (Auth)](#1-المصادقة-auth)
2. [الخدمات — Public](#2-الخدمات--public)
3. [الأقسام — Public](#3-الأقسام--public)
4. [المشاريع/الصور — Public](#4-المشاريعالصور--public)
5. [صيغة الاستجابة](#5-صيغة-الاستجابة-response-format)
6. [ملاحظات عامة](#6-ملاحظات-عامة)

---

## 1. المصادقة (Auth)

### 🔐 تسجيل الدخول (للأدمن فقط)

|            |          |
| ---------- | -------- |
| **Method** | `POST`   |
| **URL**    | `/login` |
| **Auth**   | لا يحتاج |

**Request Body (JSON):**

```json
{
    "user_name": "admin",
    "password": "Admin@1234"
}
```

**Response (200):**

```json
{
    "id": 1,
    "name": "admin",
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

> 💡 احفظ الـ `token` واستخدمه في كل طلبات الأدمن داخل الـ Header:
>
> ```
> Authorization: Bearer {token}
> ```

---

## 2. الخدمات — Public

> ✅ لا يحتاج مصادقة — يُستخدم مباشرة في الـ Frontend

---

### 📋 جلب كل الخدمات

|            |                    |
| ---------- | ------------------ |
| **Method** | `GET`              |
| **URL**    | `/public/services` |
| **Auth**   | ❌ لا يحتاج        |

**Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "تصميم داخلي",
            "icon_url": "http://localhost/NextHouse-Back/public/storage/services/icon.png"
        },
        {
            "id": 2,
            "name": "حدائق",
            "icon_url": null
        }
    ],
    "message": "..."
}
```

---

## 3. الأقسام — Public

---

### 📋 جلب أقسام خدمة معينة

|            |                                            |
| ---------- | ------------------------------------------ |
| **Method** | `GET`                                      |
| **URL**    | `/public/services/{service_id}/categories` |
| **Auth**   | ❌ لا يحتاج                                |

**مثال:** `/public/services/1/categories`

**Response (200):**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "بوهيمي",
      "service_id": 1,
      "service": {
        "id": 1,
        "name": "تصميم داخلي",
        "icon_url": "..."
      }
    },
    {
      "id": 2,
      "name": "كلاسيك",
      "service_id": 1,
      "service": { "..." }
    }
  ],
  "message": "..."
}
```

---

## 4. المشاريع/الصور — Public

---

### 🖼️ جلب صور قسم معين

|            |                                             |
| ---------- | ------------------------------------------- |
| **Method** | `GET`                                       |
| **URL**    | `/public/categories/{category_id}/projects` |
| **Auth**   | ❌ لا يحتاج                                 |

**مثال:** `/public/categories/1/projects`

**Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "image_url": "http://localhost/NextHouse-Back/public/storage/projects/abc123.jpg",
            "description": "غرفة نوم بوهيمي فاخرة",
            "category_id": 1,
            "category": {
                "id": 1,
                "name": "بوهيمي",
                "service_id": 1,
                "service": null
            }
        }
    ],
    "message": "..."
}
```

---

## 5. صيغة الاستجابة (Response Format)

جميع الـ Responses تتبع نفس الهيكل:

```json
{
  "success": true,
  "data": [ ... ],
  "message": "..."
}
```

| حقل       | النوع                   | الوصف                    |
| --------- | ----------------------- | ------------------------ |
| `success` | `boolean`               | `true` نجاح، `false` فشل |
| `data`    | `array / object / null` | البيانات المُرجعة        |
| `message` | `string`                | رسالة توضيحية            |

**Response عند الخطأ (404):**

```json
{
    "success": false,
    "data": [],
    "message": "Not Found"
}
```

---

## 6. ملاحظات عامة

### 🌐 CORS

إذا كان الـ Frontend على Domain مختلف، تحقق من إعدادات CORS في `config/cors.php` وتأكد أن الـ Domain مضاف في `allowed_origins`.

### 🖼️ الصور

- الصور مُخزّنة على السيرفر وترجع كـ URL كامل في حقل `icon_url` و `image_url`
- يمكن استخدام الـ URL مباشرة في `<img src="..." />`

### 📡 Headers الموصى بها

```
Content-Type: application/json
Accept: application/json
```

> عند رفع ملفات (FormData): احذف `Content-Type` وسيُضبط تلقائياً.

### 🔄 تدفق البيانات في الـ Frontend

```
1. GET /public/services             → اعرض قائمة الخدمات
2. عند اختيار خدمة →
   GET /public/services/{id}/categories  → اعرض أقسامها
3. عند اختيار قسم →
   GET /public/categories/{id}/projects  → اعرض صوره/مشاريعه
```

---

## 📬 Admin Endpoints (للمشرف فقط)

> هذه للإدارة فقط. تحتاج `Authorization: Bearer {token}`

| Method   | Endpoint                     | وظيفة                                                 |
| -------- | ---------------------------- | ----------------------------------------------------- |
| `POST`   | `/login`                     | تسجيل الدخول                                          |
| `GET`    | `/nexthouse/services`        | كل الخدمات                                            |
| `POST`   | `/nexthouse/services`        | إضافة خدمة (form-data: name, icon)                    |
| `POST`   | `/nexthouse/services/{id}`   | تعديل خدمة                                            |
| `DELETE` | `/nexthouse/services/{id}`   | حذف خدمة                                              |
| `GET`    | `/nexthouse/categories`      | كل الأقسام                                            |
| `POST`   | `/nexthouse/categories`      | إضافة قسم (name, service_id)                          |
| `POST`   | `/nexthouse/categories/{id}` | تعديل قسم                                             |
| `DELETE` | `/nexthouse/categories/{id}` | حذف قسم                                               |
| `GET`    | `/nexthouse/projects`        | كل الصور                                              |
| `POST`   | `/nexthouse/projects`        | رفع صورة (form-data: image, description, category_id) |
| `POST`   | `/nexthouse/projects/{id}`   | تعديل صورة                                            |
| `DELETE` | `/nexthouse/projects/{id}`   | حذف صورة                                              |
