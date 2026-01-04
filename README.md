# Payment Platform REST API

Laravel alapú fizetési platform REST API Bearer token authentikációval, amely lehetővé teszi fizetési tranzakciók kezelését, megrendelések nyilvántartását és felhasználói authentikációt.

## 🚀 Főbb funkciók

- **Authentikáció**: Regisztráció, bejelentkezés, token kezelés (Laravel Sanctum)
- **Payment CRUD műveletek**: Create, Read, Update, Delete
- **Soft Delete támogatás**: Törölt adatok visszaállíthatók
- **RESTful API**: Jól strukturált végpontok JSON válaszokkal
- **Tesztek**: Teljes körű Feature testek PHPUnit-tal

## 📋 Technológiai stack

- **Framework**: Laravel 11.x
- **Authentikáció**: JWT (tymon/jwt-auth)
- **Adatbázis**: MySQL
- **PHP verzió**: 8.2+
- **Testing**: PHPUnit

## 📊 Adatbázis struktúra

Az alkalmazás három fő táblából áll:

### Users tábla
| Mező | Típus | Leírás |
|------|-------|--------|
| id | bigint | Elsődleges kulcs |
| name | varchar(255) | Felhasználó neve |
| email | varchar(255) | Email cím (egyedi) |
| isAdmin | boolean | Admin jogosultság (default: false) |
| password | varchar(255) | Hash-elt jelszó |
| email_verified_at | timestamp | Email megerősítés időpontja |
| created_at | timestamp | Létrehozás dátuma |
| updated_at | timestamp | Utolsó módosítás dátuma |

### Orders tábla (Soft Delete)
| Mező | Típus | Leírás |
|------|-------|--------|
| id | bigint | Elsődleges kulcs |
| user_id | bigint | Foreign key (users.id) |
| total_amount | decimal(10,2) | Megrendelés teljes összege |
| status | varchar(255) | Státusz (pending, processing, completed, cancelled) |
| created_at | timestamp | Létrehozás dátuma |
| updated_at | timestamp | Utolsó módosítás dátuma |
| deleted_at | timestamp | Soft delete - törlés dátuma |

### Payments tábla (Soft Delete)
| Mező | Típus | Leírás |
|------|-------|--------|
| id | bigint | Elsődleges kulcs |
| order_id | bigint | Foreign key (orders.id) |
| payment_method | varchar(255) | Fizetési mód |
| amount | decimal(10,2) | Fizetett összeg |
| paid_at | timestamp | Fizetés időpontja |
| created_at | timestamp | Létrehozás dátuma |
| updated_at | timestamp | Utolsó módosítás dátuma |
| deleted_at | timestamp | Soft delete - törlés dátuma |

### Kapcsolatok
- Egy felhasználóhoz több megrendelés tartozhat (User → Orders: 1:N)
- Egy megrendeléshez több fizetés tartozhat (Order → Payments: 1:N)

## 🔧 Telepítés

### 1. Projekt klónozása
```bash
git clone <repository-url>
cd paymentPlatformJWT
```

### 2. Függőségek telepítése
```bash
composer install
```

### 3. JWT Secret generálása
```bash
php artisan jwt:secret
```

Ez hozzáadja a `JWT_SECRET` kulcsot a `.env` fájlhoz.

### 4. Környezeti változók beállítása
Másold le a `.env.example` fájlt `.env` néven és állítsd be az adatbázis kapcsolatot:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=paymentPlatform
DB_USERNAME=root
DB_PASSWORD=

APP_TIMEZONE=Europe/Budapest
APP_FAKER_LOCALE=hu_HU

JWT_SECRET=your_secret_key_here
JWT_TTL=60
```

### 5. Application key generálása
```bash
php artisan key:generate
```

### 6. Adatbázis létrehozása
Hozz létre egy `paymentPlatform` nevű adatbázist MySQL-ben.

### 7. Migrációk futtatása
```bash
php artisan migrate
```

### 8. Adatbázis feltöltése (Seeding)
```bash
php artisan db:seed
```

Ez létrehoz:
- **1 Kunta felhasználót**: `kunta@example.com` / `Super_Secret_Pw2025!` (Admin)
- **10 fake felhasználót**: Magyar nevekkel és adatokkal (normál felhasználók)
- **10-30 megrendelést**: Minden felhasználóhoz 1-3 megrendelés
- **10-60 fizetést**: Minden megrendeléshez 1-2 fizetés

### 9. Szerver indítása
```bash
php artisan serve
```

Az API elérhető a `http://127.0.0.1:8000/api` címen.

## 👥 Teszt felhasználók

**Kunta felhasználó (Admin):**
- Email: `kunta@example.com`
- Jelszó: `Super_Secret_Pw2025!`
- `isAdmin`: `true`

**10 fake felhasználó (normál jogosultság):**
- Magyar nevekkel (faker által generált)
- Jelszavak: faker által generált
- `isAdmin`: `false`


## 📚 API Dokumentáció

### Base URL
```
http://127.0.0.1:8000/api
```

### Headers
Minden kéréshez:
```
Content-Type: application/json
Accept: application/json
```

Védett végpontokhoz:
```
Authorization: Bearer {jwt_token}
```

**Megjegyzés:** A token JWT formátumú és a bejelentkezés (`/login`) végponton keresztül szerezhető meg.

### Nyilvános végpontok

#### GET /ping
API teszteléshez
```bash
GET /api/ping
```

**Válasz** (200 OK):
```json
{
  "message": "pong"
}
```

#### POST /register
Új felhasználó regisztrációja

**Kérés törzse**:
```json
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Válasz** (201 Created):
```json
{
  "message": "Registration successful",
  "user": {
    "id": 11,
    "name": "Test User",
    "email": "test@example.com",
    "created_at": "2025-12-04T10:30:00.000000Z",
    "updated_at": "2025-12-04T10:30:00.000000Z"
  }
}
```

#### POST /login
Bejelentkezés és token megszerzése

**Kérés törzse**:
```json
{
  "email": "kunta@example.com",
  "password": "Super_Secret_Pw2025!"
}
```

**Válasz** (200 OK):
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "name": "Kunta",
    "email": "kunta@example.com",
    "isAdmin": true
  }
}
```

### Védett végpontok (Bearer Token szükséges)

#### POST /logout
Kijelentkezés

**Válasz** (200 OK):
```json
{
  "message": "Logout successful"
}
```

#### GET /user
Saját profil lekérése

**Válasz** (200 OK):
```json
{
  "id": 1,
  "name": "Kunta",
  "email": "kunta@example.com",
  "isAdmin": true,
  "email_verified_at": null,
  "created_at": "2026-01-04T10:00:00.000000Z",
  "updated_at": "2026-01-04T10:00:00.000000Z"
}
```

#### GET /payments
Összes payment listázása

**Válasz** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "order_id": 1,
      "payment_method": "credit_card",
      "amount": "150.50",
      "paid_at": "2025-12-04T10:45:00.000000Z",
      "created_at": "2025-12-04T11:00:00.000000Z",
      "order": {
        "id": 1,
        "user_id": 1,
        "total_amount": "150.50",
        "status": "pending"
      }
    }
  ]
}
```

#### POST /payments
Új payment létrehozása

**Kérés törzse**:
```json
{
  "order_id": 1,
  "payment_method": "credit_card",
  "amount": 150.50,
  "paid_at": "2025-12-04 10:45:00"
}
```

**Válasz** (201 Created):
```json
{
  "success": true,
  "message": "Payment created successfully",
  "data": {
    "id": 1,
    "order_id": 1,
    "payment_method": "credit_card",
    "amount": "150.50",
    "paid_at": "2025-12-04T10:45:00.000000Z"
  }
}
```

#### GET /payments/{id}
Egy payment megtekintése

**Válasz** (200 OK):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "order_id": 1,
    "payment_method": "credit_card",
    "amount": "150.50"
  }
}
```

#### PUT/PATCH /payments/{id}
Payment módosítása

**PUT kérés törzse** (minden mező kötelező):
```json
{
  "order_id": 1,
  "payment_method": "bank_transfer",
  "amount": 175.00,
  "paid_at": "2025-12-04 12:00:00"
}
```

**PATCH kérés törzse** (csak a módosítandó mezők):
```json
{
  "payment_method": "stripe",
  "amount": 180.00
}
```

**Válasz** (200 OK):
```json
{
  "success": true,
  "message": "Payment updated successfully",
  "data": { }
}
```

#### DELETE /payments/{id}
Payment törlése (Soft Delete)

**Válasz** (200 OK):
```json
{
  "success": true,
  "message": "Payment deleted successfully"
}
```

## 🔐 Soft Delete

A rendszer **Soft Delete** megközelítést használ:
- Törölt rekordok fizikailag **megmaradnak** az adatbázisban
- A `deleted_at` mező kitöltésre kerül
- Lekérdezések alapértelmezetten **nem tartalmazzák** a törölt rekordokat
- Törölt rekordok később **visszaállíthatók**

## 🧪 Tesztelés

### Tesztek futtatása
```bash
php artisan test
```

### Teszt lefedettség
- **AuthTest**: 9 teszt (regisztráció, bejelentkezés, kijelentkezés)
- **PaymentTest**: 13 teszt (CRUD műveletek, validációk, authentikáció)
- **Összesen**: 25+ teszt

### Példa teszt eredmény
```
PASS  Tests\Feature\AuthTest
✓ user can register with valid data
✓ user can login with valid credentials
✓ authenticated user can logout

PASS  Tests\Feature\PaymentTest
✓ can create payment with valid data
✓ can update payment with put
✓ can delete payment (Soft Delete)

Tests:  25 passed
```

## 📝 HTTP Státuszkódok

| Kód | Jelentés | Használat |
|-----|----------|-----------|
| 200 | OK | Sikeres GET, PUT, PATCH, DELETE |
| 201 | Created | Sikeres POST (új erőforrás) |
| 400 | Bad Request | Hibás formátumú kérés |
| 401 | Unauthorized | Érvénytelen vagy hiányzó token |
| 404 | Not Found | Erőforrás nem található |
| 422 | Unprocessable Entity | Validációs hiba |

## 📁 Projekt struktúra

```
app/
├── Http/
│   └── Controllers/
│       ├── AuthController.php      # Authentikáció
│       └── PaymentController.php   # Payment CRUD
├── Models/
│   ├── User.php                    # User model
│   ├── Order.php                   # Order model (Soft Delete)
│   └── Payment.php                 # Payment model (Soft Delete)
database/
├── factories/
│   ├── OrderFactory.php            # Order factory
│   └── PaymentFactory.php          # Payment factory
├── migrations/
│   ├── *_create_users_table.php
│   ├── *_create_orders_table.php
│   ├── *_create_payments_table.php
│   ├── *_add_soft_deletes_to_orders_table.php
│   └── *_add_soft_deletes_to_payments_table.php
└── seeders/
    └── DatabaseSeeder.php          # Teszt adatok
routes/
└── api.php                         # API végpontok
tests/
├── Feature/
│   ├── AuthTest.php                # Auth tesztek
│   └── PaymentTest.php             # Payment tesztek
```

## 🛠️ Hasznos parancsok

```bash
# Migrációk visszavonása és újrafuttatása seed-del
php artisan migrate:fresh --seed

# Cache tisztítása
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Tesztek futtatása verbose móddal
php artisan test --verbose
```

## 📮 Postman Collection

A projekt tartalmaz egy teljes Postman collection-t a `docs/` mappában:
- `Payment_Platform_JWT_API.postman_collection.json`

Importáld Postman-be az egyszerű teszteléshez.

## 📄 Licenc

Ez a projekt oktatási célokat szolgál.

## 👨‍💻 Fejlesztő

Fejlesztve Laravel 11 és PHP 8.2 használatával.

---

**További dokumentáció:** A teljes API dokumentáció és megvalósítási útmutató a `docs/exampleGOOD.md` fájlban található.
