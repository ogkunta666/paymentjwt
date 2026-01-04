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

### 6. Teszt adatok feltöltése (Seeders)
```bash
php artisan db:seed
```

Ez a parancs létrehozza:
- **1 Admin felhasználót**:
  - Email: `admin@example.com`
  - Jelszó: `password123`
  - Admin jog: ✅

- **9 Normál felhasználót** magyar nevekkel:
  - Kovács János (`kovacs.janos@example.com`)
  - Nagy Péter (`nagy.peter@example.com`)
  - Szabó Anna (`szabo.anna@example.com`)
  - Tóth Eszter (`toth.eszter@example.com`)
  - Horváth Gábor (`horvath.gabor@example.com`)
  - Kiss Katalin (`kiss.katalin@example.com`)
  - Varga László (`varga.laszlo@example.com`)
  - Molnár Éva (`molnar.eva@example.com`)
  - Németh Márton (`nemeth.marton@example.com`)
  - Jelszó mindegyikhez: `password123`

- **25-35 Rendelést** (2-5 rendelés/felhasználó):
  - Véletlenszerű összegek (50 Ft - 1500 Ft)
  - Státuszok: pending, processing, completed, cancelled
  - Dátumok: utolsó 60 nap

- **30-100 Fizetést** (1-3 fizetés/rendelés):
  - Magyar fizetési módok: bankkártya, készpénz, átutalás, PayPal, Simplepay, Barion, utánvét
  - Completed rendelésekhez biztosan van fizetés dátuma

**Ha újra szeretnéd tölteni az adatokat:**
```bash
php artisan migrate:fresh --seed
```

### 7. Szerver indítása
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

#### Alapvető Tesztek - Seeder Adatokkal

**Előfeltétel:** Az adatbázis fel van töltve seeder adatokkal (`php artisan db:seed`)

1. **API Működésének Ellenőrzése**
   - Futtasd a "Test API Endpoint" kérést
   - Válasz: 200 OK

2. **Bejelentkezés Meglévő Felhasználóval**
   
   **Admin felhasználó:**
   ```json
   {
       "email": "admin@example.com",
       "password": "password123"
   }
   ```
   
   **Normál felhasználók (válassz egyet):**
   - `kovacs.janos@example.com` / password123
   - `nagy.peter@example.com` / password123
   - `szabo.anna@example.com` / password123
   - `toth.eszter@example.com` / password123
   - `horvath.gabor@example.com` / password123
   - `kiss.katalin@example.com` / password123
   - `varga.laszlo@example.com` / password123
   - `molnar.eva@example.com` / password123
   - `nemeth.marton@example.com` / password123

3. **Felhasználói Adatok Lekérése**
   - Futtasd a "Get Current User" kérést
   - Válasz: 200 OK, felhasználói adatok

#### Rendelések Tesztelése (Seeder Adatokkal)

5. **Rendelés Létrehozása**
   - Futtasd a "Create Order" kérést
   - Válasz: 201 Created

4. **Összes Rendelés Lekérése**
   - Jelentkezz be normál felhasználóval: csak az ő rendelései (2-5 db)
   - Jelentkezz be adminként: összes rendelés látható (~30 db)

5. **Egy Rendelés Lekérése**
   - Futtasd a "Get Order by ID" kérést
   - Normál user: 200 OK ha saját, 403 ha másé
   - Admin: 200 OK bármelyik rendelésnél

6. **Rendelés Módosítása**
   - Módosítsd egy rendelés státuszát vagy összegét
   - Normál user: csak saját rendelést módosíthat
   - Admin: bármit módosíthat

7. **Új Rendelés Létrehozása**
   - Futtasd a "Create Order" kérést
   - Az aktuális user-hez lesz társítva

8. **Rendelés Törlése**
   - Törölj egy rendelést
   - A hozzá tartozó fizetések is törlődnek (cascade)

#### Fizetések Tesztelése (Seeder Adatokkal)

9. **Összes Fizetés Lekérése**
    - Normál user: csak saját rendeléseihez tartozó fizetések (~5-15 db)
    - Admin: összes fizetés (~60-100 db)

10. **Egy Fizetés Lekérése**
    - Futtasd a "Get Payment by ID" kérést
    - Nézz meg egy konkrét fizetést
    - Normál user: 200 OK ha saját rendeléshez tartozik, 403 ha másé
    - Admin: 200 OK bármelyik fizetésnél

11. **Fizetés Módosítása**
    - Módosítsd a fizetési módszert vagy összeget
    - Láthatod a magyar fizetési módokat (bankkártya, PayPal, stb.)

12. **Új Fizetés Létrehozása**
    - Hozz létre új fizetést egy meglévő rendeléshez
    - Használj magyar fizetési módot

13. **Fizetés Törlése**
    - Törölj egy fizetést
    - Csak saját rendeléshez tartozót törölhetsz (vagy admin mindet)

#### Token Műveletek

14. **Token Frissítés**
    - Futtasd a "Refresh Token" kérést
    - Válasz: 200 OK, új token automatikusan mentődik

15. **Kijelentkezés**
    - Futtasd a "Logout" kérést
    - Válasz: 200 OK, token invalidálva

#### Jogosultság Tesztek

16. **Normál Felhasználó Korlátozások**
    - Jelentkezz be `kovacs.janos@example.com` felhasználóval
    - Próbálj meg hozzáférni ID 1 rendeléshez (ami valószínűleg az adminé)
    - Válasz: 403 Forbidden (ha nem a tiéd)

17. **Admin Jogosultságok**
    - Jelentkezz be `admin@example.com` felhasználóval
    - Próbálj meg hozzáférni bármely rendeléshez/fizetéshez
    - Válasz: 200 OK (admin mindent láthat és módosíthat)

#### Hibakezelés Tesztek

18. **Nem Létező Erőforrás**
    - Kérj le egy rendelést egy nem létező ID-val (pl. 9999)
    - Válasz: 404 Not Found

19. **Validációs Hibák**
    - Próbálj létrehozni rendelést negatív összeggel
    - Próbálj létrehozni fizetést nem létező order_id-val
    - Válasz: 422 Unprocessable Entity

20. **Authentikáció Nélküli Kérés**
    - Távolítsd el az Authorization headert
    - Próbálj meg lekérni rendeléseket
    - Válasz: 401 Unauthorized

### 4. Gyors Teszt Adatok Áttekintése

**Beépített felhasználók és jelszavak:**
- Admin: `admin@example.com` / `password123`
- 9 magyar felhasználó: `{vezetéknév}.{keresztnév}@example.com` / `password123`

**Adatok statisztikái:**
- ~30 rendelés összesen
- ~70-90 fizetés összesen
- Rendelés státuszok: pending, processing, completed, cancelled
- Fizetési módok: bankkártya, készpénz, átutalás, PayPal, Simplepay, Barion, utánvét
- Összegek: 50 Ft - 1500 Ft között

---

## 🛠️ Fejlesztői Megjegyzések

### Seeder Osztályok

#### UserSeeder
- Létrehoz 1 admin és 9 normál felhasználót
- Magyar nevek használata
- Email címek ékezet nélkül generálva

#### OrderSeeder
- Minden felhasználóhoz 2-5 véletlenszerű rendelés
- Különböző státuszok és összegek
- Dátumok az elmúlt 60 napból

#### PaymentSeeder
- Minden rendeléshez 1-3 fizetés
- Magyar fizetési módok
- Completed rendeléseknél garantált paid_at dátum

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
- ✅ Seederek létrehozva magyar nyelvű teszt adatokkal
- ✅ 1 admin + 9 normál felhasználó generálva
- ✅ ~30 rendelés és ~70-90 fizetés generálva
- ✅ Magyar fizetési módok (bankkártya, PayPal, Simplepay, stb.)

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
php artisan migrate:fresh --seed
```
Ez újra létrehozza az összes táblát és feltölti a teszt adatokat.

### "Duplicate entry" hiba seeder futtatáskor
```bash
php artisan migrate:fresh --seed
```
Ezzel tiszta adatbázissal kezdhetsz, törlöd a régi adatokat és újra feltöltöd.

### "Unauthenticated" hibaüzenet
- Ellenőrizd, hogy a JWT token helyesen van-e beállítva az Authorization headerben
- Formátum: `Bearer {token}`
- Ellenőrizd, hogy a token nem járt-e le (60 perc érvényesség)

### "No permission" hibák
- Ellenőrizd, hogy a megfelelő felhasználóval vagy-e bejelentkezve
- Admin műveletekhez admin jogosultság szükséges (isAdmin: true)
- Admin email: `admin@example.com`

### Tesztadatok újratöltése
Ha el akarod távolítani az összes adatot és újra feltölteni:
```bash
php artisan migrate:fresh --seed
```

---

## 📊 Adatbázis Feltöltöttség

Az adatbázis a seederek futtatása után:
- ✅ 10 felhasználó (1 admin + 9 normál)
- ✅ ~30 rendelés (változó, 2-5/user)
- ✅ ~70-90 fizetés (változó, 1-3/rendelés)
- ✅ Magyar nyelvű adatok
- ✅ Valósághű dátumok és összegek

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
