# Payment Platform REST API

Laravel alapú fizetési platform REST API Bearer token authentikációval, amely lehetővé teszi fizetési tranzakciók kezelését, megrendelések nyilvántartását és felhasználói authentikációt.

## Főbb funkciók

- **Authentikáció**: Regisztráció, bejelentkezés, token kezelés (Laravel Sanctum)
- **Payment CRUD műveletek**: Create, Read, Update, Delete
- **Soft Delete támogatás**: Törölt adatok visszaállíthatók
- **RESTful API**: Jól strukturált végpontok JSON válaszokkal
- **Tesztek**: Teljes körű Feature testek PHPUnit-tal

## Adatbázis struktúra

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

## Telepítés

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



### 5. Környezeti változók beállítása

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

### 10. Adatbázis feltöltése (Seeding)
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

## Teszt felhasználók

**Kunta felhasználó (Admin):**
- Email: `kunta@example.com`
- Jelszó: `Super_Secret_Pw2025!`
- `isAdmin`: `true`

**10 fake felhasználó (normál jogosultság):**
- Magyar nevekkel (faker által generált)
- Jelszavak: faker által generált
- `isAdmin`: `false`


## Migrációk

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

## Modellek

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

## Seeders

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

## Routes (API végpontok)

**Fájl:** `routes/api.php`

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;

// Nyilvános végpontok - JWT token nélkül elérhetők
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Teszt végpont - API működésének ellenőrzése
Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working correctly',
        'timestamp' => now()->toDateTimeString()
    ]);
});

// Védett végpontok - JWT token szükséges
Route::middleware('auth:api')->group(function () {
    // Authentikációs végpontok
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);

    // Order CRUD műveletek
    Route::apiResource('orders', OrderController::class);

    // Payment CRUD műveletek
    Route::apiResource('payments', PaymentController::class);
});
```

### Végpontok összefoglalója

| HTTP | Végpont | Védett | Leírás |
|------|---------|--------|--------|
| POST | `/api/register` | ❌ | Új felhasználó regisztrálása |
| POST | `/api/login` | ❌ | Bejelentkezés JWT token megszerzése |
| GET | `/api/test` | ❌ | API működés tesztelése |
| POST | `/api/logout` | ✅ | Kijelentkezés (token érvénytelenítése) |
| POST | `/api/refresh` | ✅ | JWT token frissítése |
| GET | `/api/me` | ✅ | Aktuális felhasználó adatai |
| GET | `/api/orders` | ✅ | Összes order listázása |
| POST | `/api/orders` | ✅ | Új order létrehozása |
| GET | `/api/orders/{id}` | ✅ | Egy order megtekintése |
| PUT/PATCH | `/api/orders/{id}` | ✅ | Order módosítása |
| DELETE | `/api/orders/{id}` | ✅ | Order törlése |
| GET | `/api/payments` | ✅ | Összes payment listázása |
| POST | `/api/payments` | ✅ | Új payment létrehozása |
| GET | `/api/payments/{id}` | ✅ | Egy payment megtekintése |
| PUT/PATCH | `/api/payments/{id}` | ✅ | Payment módosítása |
| DELETE | `/api/payments/{id}` | ✅ | Payment törlése |

---

## Controllers (Vezérlők)

### AuthController
**Fájl:** `app/Http/Controllers/AuthController.php`

Felhasználói authentikáció kezelése JWT tokenekkel.

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Új felhasználó regisztrálása
     * 
     * Validáció:
     * - name: kötelező, string, max 255 karakter
     * - email: kötelező, egyedi, valid email formátum
     * - password: kötelező, min 6 karakter, megerősítés szükséges
     * - isAdmin: opcionális, boolean (default: false)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'isAdmin' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'isAdmin' => $request->isAdmin ?? false,
        ]);

        $token = Auth::guard('api')->login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ], 201);
    }

    /**
     * Bejelentkezés JWT token megszerzése
     * 
     * Email és jelszó alapján authentikáció.
     * Sikeres bejelentkezés esetén JWT tokent ad vissza.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = Auth::guard('api')->user();

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * Kijelentkezés
     * 
     * Az aktuális JWT token érvénytelenítése.
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * JWT token frissítése
     * 
     * Új tokent generál a régi helyett, meghosszabbítva a session-t.
     */
    public function refresh()
    {
        $token = Auth::guard('api')->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Token refreshed successfully',
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * Aktuális felhasználó adatainak lekérése
     * 
     * Visszaadja a JWT tokenből azonosított felhasználót.
     */
    public function me()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::guard('api')->user()
        ]);
    }
}
```

### OrderController
**Fájl:** `app/Http/Controllers/OrderController.php`

Order (megrendelés) CRUD műveletek jogosultság-kezeléssel.

```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Order-ek listázása
     * 
     * - Admin: látja az összes order-t
     * - Normál user: csak a sajátjait
     */
    public function index()
    {
        $user = Auth::guard('api')->user();
        
        if ($user->isAdmin) {
            $orders = Order::with('user', 'payments')->get();
        } else {
            $orders = Order::where('user_id', $user->id)->with('payments')->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    /**
     * Új order létrehozása
     * 
     * Automatikusan az aktuális felhasználóhoz rendeli.
     * Status alapértelmezetten 'pending'.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total_amount' => 'required|numeric|min:0',
            'status' => 'string|in:pending,processing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::guard('api')->user();

        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => $request->total_amount,
            'status' => $request->status ?? 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully',
            'data' => $order->load('payments')
        ], 201);
    }

    /**
     * Egy order megtekintése
     * 
     * Jogosultság ellenőrzés:
     * - Admin: bármely order-t megtekintheti
     * - User: csak a saját order-jét
     */
    public function show(string $id)
    {
        $order = Order::with('user', 'payments')->find($id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found'
            ], 404);
        }

        $user = Auth::guard('api')->user();

        if (!$user->isAdmin && $order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No permission to view this order'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }

    /**
     * Order módosítása
     * 
     * Jogosultság: Admin vagy az order tulajdonosa
     */
    public function update(Request $request, string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found'
            ], 404);
        }

        $user = Auth::guard('api')->user();

        if (!$user->isAdmin && $order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No permission to update this order'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'total_amount' => 'numeric|min:0',
            'status' => 'string|in:pending,processing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->update($request->only(['total_amount', 'status']));

        return response()->json([
            'status' => 'success',
            'message' => 'Order updated successfully',
            'data' => $order->load('payments')
        ]);
    }

    /**
     * Order törlése
     * 
     * Jogosultság: Admin vagy az order tulajdonosa
     * Cascade delete: a hozzá tartozó payments is törlődnek
     */
    public function destroy(string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found'
            ], 404);
        }

        $user = Auth::guard('api')->user();

        if (!$user->isAdmin && $order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No permission to delete this order'
            ], 403);
        }

        $order->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Order deleted successfully'
        ]);
    }
}
```

### PaymentController
**Fájl:** `app/Http/Controllers/PaymentController.php`

Payment (fizetés) CRUD műveletek jogosultság-kezeléssel.

```php
<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Payment-ek listázása
     * 
     * - Admin: összes payment
     * - User: csak a saját order-jeihez tartozó payments
     */
    public function index()
    {
        $user = Auth::guard('api')->user();
        
        if ($user->isAdmin) {
            $payments = Payment::with('order.user')->get();
        } else {
            $payments = Payment::whereHas('order', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with('order')->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $payments
        ]);
    }

    /**
     * Új payment létrehozása
     * 
     * Jogosultság: csak a saját order-jéhez hozhat létre payment-et
     * (kivéve admin, aki bárkihez)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'paid_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::find($request->order_id);
        $user = Auth::guard('api')->user();

        if (!$user->isAdmin && $order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No permission to create payment for this order'
            ], 403);
        }

        $payment = Payment::create([
            'order_id' => $request->order_id,
            'payment_method' => $request->payment_method,
            'amount' => $request->amount,
            'paid_at' => $request->paid_at ?? now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment created successfully',
            'data' => $payment->load('order')
        ], 201);
    }

    /**
     * Egy payment megtekintése
     * 
     * Jogosultság: Admin vagy a payment order-jének tulajdonosa
     */
    public function show(string $id)
    {
        $payment = Payment::with('order.user')->find($id);

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found'
            ], 404);
        }

        $user = Auth::guard('api')->user();

        if (!$user->isAdmin && $payment->order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No permission to view this payment'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $payment
        ]);
    }

    /**
     * Payment módosítása
     * 
     * Jogosultság: Admin vagy az order tulajdonosa
     */
    public function update(Request $request, string $id)
    {
        $payment = Payment::with('order')->find($id);

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found'
            ], 404);
        }

        $user = Auth::guard('api')->user();

        if (!$user->isAdmin && $payment->order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No permission to update this payment'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'string|max:255',
            'amount' => 'numeric|min:0',
            'paid_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment->update($request->only(['payment_method', 'amount', 'paid_at']));

        return response()->json([
            'status' => 'success',
            'message' => 'Payment updated successfully',
            'data' => $payment->load('order')
        ]);
    }

    /**
     * Payment törlése
     * 
     * Jogosultság: Admin vagy az order tulajdonosa
     */
    public function destroy(string $id)
    {
        $payment = Payment::with('order')->find($id);

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found'
            ], 404);
        }

        $user = Auth::guard('api')->user();

        if (!$user->isAdmin && $payment->order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No permission to delete this payment'
            ], 403);
        }

        $payment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment deleted successfully'
        ]);
    }
}
```

---

## Jogosultságkezelés

### Admin vs Normál felhasználó

**Admin jogosultságok (`isAdmin = true`):**
-  Látja az **összes** order-t és payment-et
-  Módosíthatja és törölheti **bármely** felhasználó adatait
-  Teljes hozzáférés az API minden funkciójához

**Normál felhasználó jogosultságok (`isAdmin = false`):**
-  Csak a **saját** order-jeit látja
-  Csak a **saját** order-jeihez tartozó payment-eket látja
-  Csak a **saját** adatait módosíthatja és törölheti
-  Más felhasználók adataihoz nincs hozzáférése

### Jogosultság ellenőrzés működése

```php
// Controller-ekben minden műveletnél ellenőrzés
if (!$user->isAdmin && $order->user_id !== $user->id) {
    return response()->json([
        'status' => 'error',
        'message' => 'No permission to view this order'
    ], 403);
}
```

---

## API Dokumentáció

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

## Soft Delete

A rendszer **Soft Delete** megközelítést használ:
- Törölt rekordok fizikailag **megmaradnak** az adatbázisban
- A `deleted_at` mező kitöltésre kerül
- Lekérdezések alapértelmezetten **nem tartalmazzák** a törölt rekordokat
- Törölt rekordok később **visszaállíthatók**

## Tesztelés

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

## HTTP Státuszkódok

| Kód | Jelentés | Használat |
|-----|----------|-----------|
| 200 | OK | Sikeres GET, PUT, PATCH, DELETE |
| 201 | Created | Sikeres POST (új erőforrás) |
| 400 | Bad Request | Hibás formátumú kérés |
| 401 | Unauthorized | Érvénytelen vagy hiányzó token |
| 404 | Not Found | Erőforrás nem található |
| 422 | Unprocessable Entity | Validációs hiba |

## Projekt struktúra

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

## Hasznos parancsok

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

