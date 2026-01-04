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

### 1. Projekt létrehozása
```bash
composer create-project laravel/laravel paymentPlatformJWT
cd paymentPlatformJWT
```

### 2. JWT Auth csomag telepítése
```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

### 3. JWT Secret generálása
```bash
php artisan jwt:secret
```

Ez hozzáadja a `JWT_SECRET` kulcsot a `.env` fájlhoz.

### 4. Migrációk létrehozása
```bash
php artisan make:migration add_is_admin_to_users_table
php artisan make:migration create_orders_table
php artisan make:migration create_payments_table
```

**Megjegyzés:** A migrációs fájlokat a `database/migrations/` mappában kell szerkeszteni a megfelelő sémával (lásd az adatbázis struktúra részt).

### 5. Környezeti változók beállítása
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

### 6. Application key generálása
```bash
php artisan key:generate
```

### 7. Adatbázis létrehozása
Hozz létre egy `paymentPlatform` nevű adatbázist MySQL-ben.

### 8. Migrációk futtatása
```bash
php artisan migrate
```

### 9. Modellek és Factory-k létrehozása
```bash
php artisan make:model Order -mf
php artisan make:model Payment -mf
```

**Megjegyzés:** Szerkeszd a modelleket, factory-kat és seeder-eket a megfelelő kapcsolatokkal és adatokkal.

### 10. Adatbázis feltöltése (Seeding))
```bash
php artisan db:seed
```

Ez létrehoz:
- **1 Kunta felhasználót**: `kunta@example.com` / `Super_Secret_Pw2025!` (Admin)
- **10 fake felhasználót**: Magyar nevekkel és adatokkal (normál felhasználók)
- **10-30 megrendelést**: Minden felhasználóhoz 1-3 megrendelés
- **10-60 fizetést**: Minden megrendeléshez 1-2 fizetés

### 11. Szerver indítása
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


## 📂 Migrációk

### 1. Add isAdmin to Users Table
**Fájl:** `database/migrations/2026_01_04_112446_add_is_admin_to_users_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('isAdmin')->default(false)->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('isAdmin');
        });
    }
};
```

### 2. Create Orders Table
**Fájl:** `database/migrations/2026_01_04_112455_create_orders_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
```

### 3. Create Payments Table
**Fájl:** `database/migrations/2026_01_04_112503_create_payments_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('payment_method');
            $table->decimal('amount', 10, 2);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
```

---

## 🎯 Modellek

### User Model
**Fájl:** `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'isAdmin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'isAdmin' => 'boolean',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
```

### Order Model
**Fájl:** `app/Models/Order.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
```

### Payment Model
**Fájl:** `app/Models/Payment.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
```

---

## 🌱 Seeders

### DatabaseSeeder
**Fájl:** `database/seeders/DatabaseSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            OrderSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
```

### UserSeeder
**Fájl:** `database/seeders/UserSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Magyar nevek listája
        $magyarNevek = [
            'Kovács János',
            'Nagy Péter',
            'Szabó Anna',
            'Tóth Eszter',
            'Horváth Gábor',
            'Kiss Katalin',
            'Varga László',
            'Molnár Éva',
            'Németh Márton'
        ];

        // Admin felhasználó (Kunta)
        User::create([
            'name' => 'Kunta',
            'email' => 'kunta@example.com',
            'password' => Hash::make('Super_Secret_Pw2025!'),
            'isAdmin' => true,
        ]);

        // 9 normál felhasználó magyar nevekkel
        foreach ($magyarNevek as $nev) {
            $emailNev = strtolower(str_replace(' ', '.', $nev));
            $emailNev = $this->removeAccents($emailNev);
            
            User::create([
                'name' => $nev,
                'email' => $emailNev . '@example.com',
                'password' => Hash::make('password123'),
                'isAdmin' => false,
            ]);
        }
    }

    private function removeAccents($string)
    {
        $accents = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ö' => 'o', 
            'ő' => 'o', 'ú' => 'u', 'ü' => 'u', 'ű' => 'u',
        ];
        return strtr($string, $accents);
    }
}
```

### OrderSeeder
**Fájl:** `database/seeders/OrderSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $statuses = ['pending', 'processing', 'completed', 'cancelled'];

        // Minden felhasználóhoz 2-5 random rendelés
        foreach ($users as $user) {
            $orderCount = rand(2, 5);
            
            for ($i = 0; $i < $orderCount; $i++) {
                Order::create([
                    'user_id' => $user->id,
                    'total_amount' => rand(5000, 150000) / 100,
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => now()->subDays(rand(0, 60)),
                    'updated_at' => now()->subDays(rand(0, 30)),
                ]);
            }
        }
    }
}
```

### PaymentSeeder
**Fájl:** `database/seeders/PaymentSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMethods = [
            'bankkártya',
            'készpénz',
            'átutalás',
            'PayPal',
            'Simplepay',
            'Barion',
            'utánvét'
        ];

        $orders = Order::all();

        foreach ($orders as $order) {
            // Minden rendeléshez 1-3 fizetés
            $paymentCount = rand(1, 3);
            $remainingAmount = $order->total_amount;
            
            for ($i = 0; $i < $paymentCount; $i++) {
                if ($i == $paymentCount - 1) {
                    $amount = $remainingAmount;
                } else {
                    $maxAmount = $remainingAmount * 0.7;
                    $amount = rand(100, (int)($maxAmount * 100)) / 100;
                    $remainingAmount -= $amount;
                }

                $paidAt = null;
                if ($order->status == 'completed' || rand(0, 100) > 30) {
                    $paidAt = $order->created_at->addDays(rand(0, 5));
                }

                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'amount' => round($amount, 2),
                    'paid_at' => $paidAt,
                    'created_at' => $order->created_at->addMinutes(rand(5, 120)),
                ]);
            }
        }
    }
}
```

---

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

# Csak migrációk futtatása (adatok törlése nélkül)
php artisan migrate

# Migrációk visszavonása
php artisan migrate:rollback

# Migrációk státusza
php artisan migrate:status

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
