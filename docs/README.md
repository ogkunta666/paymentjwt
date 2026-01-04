# Online Fizetések Nyilvántartása - Teljes Dokumentáció

## 📋 Projekt Áttekintés

Ez a Laravel alapú API rendszer online fizetések és rendelések nyilvántartására szolgál JWT token alapú authentikációval. A rendszer támogatja a többszintű jogosultság kezelést (admin és felhasználó szerepkörök) és teljes CRUD műveleteket biztosít.

## 🗄️ Adatbázis Struktúra

### Users tábla
```
- id (bigint, primary key, auto increment)
- name (string)
- email (string, unique)
- password (string, hashed)
- isAdmin (boolean, default: false)
- email_verified_at (timestamp, nullable)
- remember_token (string, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### Orders tábla
```
- id (bigint, primary key, auto increment)
- user_id (bigint, foreign key -> users.id, cascade on delete)
- total_amount (decimal 10,2)
- status (string, default: 'pending')
- created_at (timestamp)
- updated_at (timestamp)
```

### Payments tábla
```
- id (bigint, primary key, auto increment)
- order_id (bigint, foreign key -> orders.id, cascade on delete)
- payment_method (string)
- amount (decimal 10,2)
- paid_at (timestamp, nullable)
- created_at (timestamp)
```

## 🔐 Authentikáció

A rendszer JWT (JSON Web Token) alapú authentikációt használ a `tymon/jwt-auth` csomag segítségével.

### JWT Konfiguráció
- Token érvényesség: 60 perc (konfigurálható a `config/jwt.php` fájlban)
- Refresh token érvényesség: 2 hét
- Guard: `api`

## 🚀 Telepítés és Beállítás

### 1. Függőségek telepítése
```bash
composer install
```

### 2. Környezeti változók beállítása
Másold le a `.env.example` fájlt `.env` néven és állítsd be az adatbázis kapcsolatot:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=payment_platform
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Alkalmazás kulcs generálása
```bash
php artisan key:generate
```

### 4. JWT titkos kulcs generálása
```bash
php artisan jwt:secret
```

### 5. Adatbázis migrációk futtatása
```bash
php artisan migrate
```

### 6. Szerver indítása
```bash
php artisan serve
```

Az API elérhető lesz a `http://localhost:8000/api` címen.

## 📚 API Endpointok

### Base URL
```
http://localhost:8000/api
```

---

## 🔑 Authentikáció Endpointok

### 1. Regisztráció
**POST** `/register`

**Request Body:**
```json
{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "isAdmin": false
}
```

**Sikeres Válasz (201):**
```json
{
    "status": "success",
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "Test User",
        "email": "test@example.com",
        "isAdmin": false,
        "created_at": "2026-01-04T10:00:00.000000Z",
        "updated_at": "2026-01-04T10:00:00.000000Z"
    },
    "authorization": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "type": "bearer"
    }
}
```

**Hibakódok:**
- **422**: Validációs hiba (hiányzó vagy hibás mezők)

---

### 2. Bejelentkezés
**POST** `/login`

**Request Body:**
```json
{
    "email": "test@example.com",
    "password": "password123"
}
```

**Sikeres Válasz (200):**
```json
{
    "status": "success",
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "Test User",
        "email": "test@example.com",
        "isAdmin": false
    },
    "authorization": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "type": "bearer"
    }
}
```

**Hibakódok:**
- **401**: Unauthorized (hibás email vagy jelszó)
- **422**: Validációs hiba

---

### 3. Kijelentkezés
**POST** `/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Sikeres Válasz (200):**
```json
{
    "status": "success",
    "message": "Successfully logged out"
}
```

---

### 4. Token Frissítés
**POST** `/refresh`

**Headers:**
```
Authorization: Bearer {token}
```

**Sikeres Válasz (200):**
```json
{
    "status": "success",
    "message": "Token refreshed successfully",
    "authorization": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "type": "bearer"
    }
}
```

---

### 5. Aktuális Felhasználó Lekérése
**GET** `/me`

**Headers:**
```
Authorization: Bearer {token}
```

**Sikeres Válasz (200):**
```json
{
    "status": "success",
    "user": {
        "id": 1,
        "name": "Test User",
        "email": "test@example.com",
        "isAdmin": false,
        "created_at": "2026-01-04T10:00:00.000000Z",
        "updated_at": "2026-01-04T10:00:00.000000Z"
    }
}
```

---

## 📦 Orders Endpointok

### 1. Összes Rendelés Lekérése
**GET** `/orders`

**Headers:**
```
Authorization: Bearer {token}
```

**Sikeres Válasz (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "total_amount": "15000.50",
            "status": "pending",
            "created_at": "2026-01-04T10:00:00.000000Z",
            "updated_at": "2026-01-04T10:00:00.000000Z",
            "user": {
                "id": 1,
                "name": "Test User",
                "email": "test@example.com"
            },
            "payments": []
        }
    ]
}
```

**Megjegyzés:** Admin felhasználók az összes rendelést látják, normál felhasználók csak a sajátjaikat.

---

### 2. Egy Rendelés Lekérése
**GET** `/orders/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Sikeres Válasz (200):**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "user_id": 1,
        "total_amount": "15000.50",
        "status": "pending",
        "created_at": "2026-01-04T10:00:00.000000Z",
        "updated_at": "2026-01-04T10:00:00.000000Z",
        "user": {
            "id": 1,
            "name": "Test User",
            "email": "test@example.com"
        },
        "payments": []
    }
}
```

**Hibakódok:**
- **404**: Order not found
- **403**: No permission to view this order

---

### 3. Rendelés Létrehozása
**POST** `/orders`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "total_amount": 15000.50,
    "status": "pending"
}
```

**Megjegyzés:** A `status` opcionális. Lehetséges értékek: `pending`, `processing`, `completed`, `cancelled`. Alapértelmezett: `pending`.

**Sikeres Válasz (201):**
```json
{
    "status": "success",
    "message": "Order created successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "total_amount": "15000.50",
        "status": "pending",
        "created_at": "2026-01-04T10:00:00.000000Z",
        "updated_at": "2026-01-04T10:00:00.000000Z",
        "payments": []
    }
}
```

**Hibakódok:**
- **422**: Validation failed

---

### 4. Rendelés Módosítása
**PUT** `/orders/{id}`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "total_amount": 18000.75,
    "status": "completed"
}
```

**Sikeres Válasz (200):**
```json
{
    "status": "success",
    "message": "Order updated successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "total_amount": "18000.75",
        "status": "completed",
        "created_at": "2026-01-04T10:00:00.000000Z",
        "updated_at": "2026-01-04T11:00:00.000000Z",
        "payments": []
    }
}
```

**Hibakódok:**
- **404**: Order not found
- **403**: No permission to update this order
- **422**: Validation failed

---

### 5. Rendelés Törlése
**DELETE** `/orders/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Sikeres Válasz (200):**
```json
{
    "status": "success",
    "message": "Order deleted successfully"
}
```

**Hibakódok:**
- **404**: Order not found
- **403**: No permission to delete this order

**Megjegyzés:** A rendeléshez tartozó összes fizetés is törlődik (cascade delete).

---

## 💳 Payments Endpointok

### 1. Összes Fizetés Lekérése
**GET** `/payments`

**Headers:**
```
Authorization: Bearer {token}
```

**Sikeres Válasz (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "order_id": 1,
            "payment_method": "credit_card",
            "amount": "15000.50",
            "paid_at": "2026-01-04T12:30:00.000000Z",
            "created_at": "2026-01-04T12:30:00.000000Z",
            "order": {
                "id": 1,
                "user_id": 1,
                "total_amount": "15000.50",
                "status": "completed",
                "user": {
                    "id": 1,
                    "name": "Test User",
                    "email": "test@example.com"
                }
            }
        }
    ]
}
```

**Megjegyzés:** Admin felhasználók az összes fizetést látják, normál felhasználók csak a saját rendeléseikhez tartozókat.

---

### 2. Egy Fizetés Lekérése
**GET** `/payments/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Sikeres Válasz (200):**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "order_id": 1,
        "payment_method": "credit_card",
        "amount": "15000.50",
        "paid_at": "2026-01-04T12:30:00.000000Z",
        "created_at": "2026-01-04T12:30:00.000000Z",
        "order": {
            "id": 1,
            "user_id": 1,
            "total_amount": "15000.50",
            "status": "completed"
        }
    }
}
```

**Hibakódok:**
- **404**: Payment not found
- **403**: No permission to view this payment

---

### 3. Fizetés Létrehozása
**POST** `/payments`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "order_id": 1,
    "payment_method": "credit_card",
    "amount": 15000.50,
    "paid_at": "2026-01-04 12:30:00"
}
```

**Megjegyzés:** A `paid_at` opcionális. Ha nem adod meg, az aktuális időpont kerül rögzítésre.

**Sikeres Válasz (201):**
```json
{
    "status": "success",
    "message": "Payment created successfully",
    "data": {
        "id": 1,
        "order_id": 1,
        "payment_method": "credit_card",
        "amount": "15000.50",
        "paid_at": "2026-01-04T12:30:00.000000Z",
        "created_at": "2026-01-04T12:30:00.000000Z",
        "order": {
            "id": 1,
            "user_id": 1,
            "total_amount": "15000.50",
            "status": "pending"
        }
    }
}
```

**Hibakódok:**
- **422**: Validation failed (hiányzó order_id vagy nem létező order)
- **403**: No permission to create payment for this order

---

### 4. Fizetés Módosítása
**PUT** `/payments/{id}`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "payment_method": "bank_transfer",
    "amount": 16000.00,
    "paid_at": "2026-01-04 14:00:00"
}
```

**Sikeres Válasz (200):**
```json
{
    "status": "success",
    "message": "Payment updated successfully",
    "data": {
        "id": 1,
        "order_id": 1,
        "payment_method": "bank_transfer",
        "amount": "16000.00",
        "paid_at": "2026-01-04T14:00:00.000000Z",
        "created_at": "2026-01-04T12:30:00.000000Z",
        "order": {
            "id": 1,
            "user_id": 1,
            "total_amount": "15000.50",
            "status": "completed"
        }
    }
}
```

**Hibakódok:**
- **404**: Payment not found
- **403**: No permission to update this payment
- **422**: Validation failed

---

### 5. Fizetés Törlése
**DELETE** `/payments/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Sikeres Válasz (200):**
```json
{
    "status": "success",
    "message": "Payment deleted successfully"
}
```

**Hibakódok:**
- **404**: Payment not found
- **403**: No permission to delete this payment

---

## 🧪 Test Endpoint

### API Teszt
**GET** `/test`

**Sikeres Válasz (200):**
```json
{
    "status": "success",
    "message": "API is working correctly",
    "timestamp": "2026-01-04 12:00:00"
}
```

**Megjegyzés:** Ez egy publikus endpoint, nem szükséges authentikáció.

---

## 🔒 Jogosultságkezelés

### Admin Jogosultságok
- Láthatja az **összes** felhasználó rendelését és fizetését
- Módosíthat és törölhet **bármely** rendelést és fizetést
- Létrehozhat fizetést **bármely** rendeléshez

### Normál Felhasználói Jogosultságok
- Csak a **saját** rendeléseit és fizetéseit láthatja
- Csak a **saját** rendeléseit és fizetéseit módosíthatja és törölheti
- Csak a **saját** rendeléseihez hozhat létre fizetést

### Admin Felhasználó Létrehozása
A regisztráció során állítsd be az `isAdmin` mezőt `true` értékre:
```json
{
    "name": "Admin User",
    "email": "admin@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "isAdmin": true
}
```

---

## 📊 HTTP Státuszkódok és Hibakezelés

### Sikeres Műveletek
- **200 OK**: Sikeres lekérés, módosítás vagy törlés
- **201 Created**: Sikeres létrehozás

### Hibakódok

#### 401 Unauthorized
```json
{
    "status": "error",
    "message": "Unauthorized"
}
```
**Okok:**
- Hibás bejelentkezési adatok
- Hiányzó vagy érvénytelen JWT token

#### 403 Forbidden
```json
{
    "status": "error",
    "message": "No permission to view/update/delete this order/payment"
}
```
**Okok:**
- A felhasználó megpróbál hozzáférni más felhasználó adataihoz
- Nincs admin jogosultsága a művelethez

#### 404 Not Found
```json
{
    "status": "error",
    "message": "Order not found"
}
```
vagy
```json
{
    "status": "error",
    "message": "Payment not found"
}
```
**Okok:**
- A megadott ID-val nem létezik erőforrás

#### 422 Unprocessable Entity
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "email": [
            "The email field is required."
        ],
        "password": [
            "The password must be at least 6 characters."
        ]
    }
}
```
**Okok:**
- Validációs szabályok megsértése
- Hiányzó kötelező mezők
- Hibás formátumú adatok

---

## 🧪 Tesztelési Útmutató

### 1. Postman Collection Importálása

1. Nyisd meg a Postmant
2. Kattints az **Import** gombra
3. Válaszd a `docs/Payment_Platform_JWT_API.postman_collection.json` fájlt
4. A collection automatikusan importálódik az összes endpointtal

### 2. Környezeti Változók Beállítása

A Postman collection automatikusan kezeli a következőket:
- `base_url`: http://localhost:8000/api
- `token`: Automatikusan frissül bejelentkezés/regisztráció után

### 3. Tesztelési Forgatókönyv

#### Alapvető Tesztek

1. **API Működésének Ellenőrzése**
   - Futtasd a "Test API Endpoint" kérést
   - Válasz: 200 OK

2. **Regisztráció**
   - Futtasd a "Register User" kérést egy normál felhasználóval
   - Futtasd a "Register Admin" kérést egy admin felhasználóval
   - Válasz: 201 Created, token automatikusan mentődik

3. **Bejelentkezés**
   - Futtasd a "Login" kérést
   - Válasz: 200 OK, token automatikusan mentődik

4. **Felhasználói Adatok Lekérése**
   - Futtasd a "Get Current User" kérést
   - Válasz: 200 OK, felhasználói adatok

#### Rendelések Tesztelése

5. **Rendelés Létrehozása**
   - Futtasd a "Create Order" kérést
   - Válasz: 201 Created

6. **Összes Rendelés Lekérése**
   - Futtasd a "Get All Orders" kérést
   - Normál felhasználó: csak saját rendelések
   - Admin: összes rendelés

7. **Egy Rendelés Lekérése**
   - Futtasd a "Get Order by ID" kérést (ID: 1)
   - Válasz: 200 OK vagy 403 ha nem sajátod és nem vagy admin

8. **Rendelés Módosítása**
   - Futtasd az "Update Order" kérést
   - Válasz: 200 OK vagy 403 ha nincs jogod

9. **Rendelés Törlése**
   - Futtasd a "Delete Order" kérést
   - Válasz: 200 OK vagy 403 ha nincs jogod

#### Fizetések Tesztelése

10. **Fizetés Létrehozása**
    - Először hozz létre egy rendelést
    - Futtasd a "Create Payment" kérést a rendelés ID-jával
    - Válasz: 201 Created

11. **Összes Fizetés Lekérése**
    - Futtasd a "Get All Payments" kérést
    - Normál felhasználó: csak saját rendelésekhez tartozó fizetések
    - Admin: összes fizetés

12. **Egy Fizetés Lekérése**
    - Futtasd a "Get Payment by ID" kérést
    - Válasz: 200 OK vagy 403 ha nem sajátod és nem vagy admin

13. **Fizetés Módosítása**
    - Futtasd az "Update Payment" kérést
    - Válasz: 200 OK vagy 403 ha nincs jogod

14. **Fizetés Törlése**
    - Futtasd a "Delete Payment" kérést
    - Válasz: 200 OK vagy 403 ha nincs jogod

#### Token Műveletek

15. **Token Frissítés**
    - Futtasd a "Refresh Token" kérést
    - Válasz: 200 OK, új token automatikusan mentődik

16. **Kijelentkezés**
    - Futtasd a "Logout" kérést
    - Válasz: 200 OK, token invalidálva

#### Jogosultság Tesztek

17. **Normál Felhasználó Korlátozások**
    - Jelentkezz be normál felhasználóként
    - Próbálj meg hozzáférni más felhasználó rendeléséhez
    - Válasz: 403 Forbidden

18. **Admin Jogosultságok**
    - Jelentkezz be adminként
    - Próbálj meg hozzáférni bármely rendeléshez/fizetéshez
    - Válasz: 200 OK

#### Hibakezelés Tesztek

19. **Nem Létező Erőforrás**
    - Kérj le egy rendelést egy nem létező ID-val (pl. 9999)
    - Válasz: 404 Not Found

20. **Validációs Hibák**
    - Próbálj létrehozni rendelést negatív összeggel
    - Próbálj létrehozni fizetést nem létező order_id-val
    - Válasz: 422 Unprocessable Entity

21. **Authentikáció Nélküli Kérés**
    - Távolítsd el az Authorization headert
    - Próbálj meg lekérni rendeléseket
    - Válasz: 401 Unauthorized

---

## 🛠️ Fejlesztői Megjegyzések

### Model Kapcsolatok

#### User Model
```php
public function orders()
{
    return $this->hasMany(Order::class);
}
```

#### Order Model
```php
public function user()
{
    return $this->belongsTo(User::class);
}

public function payments()
{
    return $this->hasMany(Payment::class);
}
```

#### Payment Model
```php
public function order()
{
    return $this->belongsTo(Order::class);
}
```

### Validációs Szabályok

#### Regisztráció
- `name`: kötelező, string, max 255 karakter
- `email`: kötelező, string, email formátum, egyedi, max 255 karakter
- `password`: kötelező, string, min 6 karakter, megerősítés szükséges
- `isAdmin`: opcionális, boolean

#### Bejelentkezés
- `email`: kötelező, string, email formátum
- `password`: kötelező, string

#### Order Létrehozás/Módosítás
- `total_amount`: kötelező (létrehozásnál), szám, min 0
- `status`: opcionális, string, értékek: pending, processing, completed, cancelled

#### Payment Létrehozás/Módosítás
- `order_id`: kötelező (létrehozásnál), létező order ID
- `payment_method`: kötelező (létrehozásnál), string, max 255 karakter
- `amount`: kötelező (létrehozásnál), szám, min 0
- `paid_at`: opcionális, dátum formátum

### Cascade Delete

- Order törlésekor a hozzá tartozó összes Payment is törlődik
- User törlésekor a hozzá tartozó összes Order (és így a Payments is) törlődik

---

## 📝 Változtatási Napló

### v1.0.0 (2026-01-04)
- ✅ Alapvető JWT authentikáció implementálva
- ✅ User model isAdmin mezővel kibővítve
- ✅ Order és Payment modellek létrehozva
- ✅ Teljes CRUD műveletek Orders-hez
- ✅ Teljes CRUD műveletek Payments-hez
- ✅ Jogosultságkezelés implementálva (admin/user)
- ✅ API hibakódok standardizálva
- ✅ Postman collection létrehozva
- ✅ Teljes dokumentáció elkészítve

---

## 🐛 Hibaelhárítás

### "Class 'Tymon\JWTAuth\Providers\LaravelServiceProvider' not found"
```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

### "SQLSTATE[42S02]: Base table or view not found"
```bash
php artisan migrate:fresh
```

### "Unauthenticated" hibaüzenet
- Ellenőrizd, hogy a JWT token helyesen van-e beállítva az Authorization headerben
- Formátum: `Bearer {token}`
- Ellenőrizd, hogy a token nem járt-e le (60 perc érvényesség)

### "No permission" hibák
- Ellenőrizd, hogy a megfelelő felhasználóval vagy-e bejelentkezve
- Admin műveletekhez admin jogosultság szükséges (isAdmin: true)

---

## 📞 Támogatás

Kérdések vagy problémák esetén nyiss egy issue-t a projekt repository-jában.

---

## 📄 Licensz

Ez a projekt oktatási célokra készült.

---

**Készítve:** 2026-01-04  
**Verzió:** 1.0.0  
**Laravel Verzió:** 11.x  
**PHP Verzió:** 8.2+
