# 💳 Online Fizetések Nyilvántartása - Payment Platform JWT

Laravel alapú REST API rendszer online fizetések és rendelések nyilvántartására JWT token authentikációval.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## 🚀 Gyors Indítás

### Telepítés

```bash
# Függőségek telepítése
composer install

# Környezeti változók beállítása
cp .env.example .env

# Alkalmazás kulcs generálása
php artisan key:generate

# JWT titkos kulcs generálása
php artisan jwt:secret

# Adatbázis létrehozása és migrációk futtatása
php artisan migrate

# Teszt adatok feltöltése (1 admin + 9 felhasználó, ~30 rendelés, ~70-90 fizetés)
php artisan db:seed

# Szerver indítása
php artisan serve
```

Az API elérhető lesz: `http://localhost:8000/api`

## 👤 Beépített Felhasználók

### Admin Felhasználó
- **Email:** `admin@example.com`
- **Jelszó:** `password123`
- **Jogosultságok:** Teljes hozzáférés az összes adathoz

### Normál Felhasználók (9 db - Magyar nevek)
- `kovacs.janos@example.com`
- `nagy.peter@example.com`
- `szabo.anna@example.com`
- `toth.eszter@example.com`
- `horvath.gabor@example.com`
- `kiss.katalin@example.com`
- `varga.laszlo@example.com`
- `molnar.eva@example.com`
- `nemeth.marton@example.com`

**Mindegyik jelszava:** `password123`

## 📋 Funkciók

### ✅ JWT Authentikáció
- Register, Login, Logout, Token Refresh
- Bearer token alapú védelem

### ✅ Jogosultságkezelés
- **Admin:** Teljes hozzáférés minden adathoz
- **User:** Csak saját rendelések és fizetések kezelése

### ✅ CRUD Műveletek
- **Users:** Regisztráció, bejelentkezés
- **Orders:** Teljes CRUD (Create, Read, Update, Delete)
- **Payments:** Teljes CRUD

### ✅ Validáció és Hibakezelés
- 200 OK - Sikeres művelet
- 201 Created - Sikeres létrehozás
- 401 Unauthorized - Érvénytelen authentikáció
- 403 Forbidden - Nincs jogosultság
- 404 Not Found - Nem található
- 422 Validation Failed - Validációs hiba

## 📦 Adatbázis Struktúra

```
users
├── id
├── name
├── email
├── password
├── isAdmin (boolean)
└── timestamps

orders
├── id
├── user_id (FK -> users)
├── total_amount (decimal)
├── status (pending/processing/completed/cancelled)
└── timestamps

payments
├── id
├── order_id (FK -> orders)
├── payment_method (string)
├── amount (decimal)
├── paid_at (timestamp)
└── created_at
```

## 🧪 Tesztelés Postman-nel

1. **Importáld a Postman collection-t:**
   - Fájl: `docs/Payment_Platform_JWT_API.postman_collection.json`
   - Postman → Import → File → Válaszd ki a fájlt

2. **A collection tartalmazza:**
   - ✅ Minden API endpointot
   - ✅ Automatikus token kezelést
   - ✅ Példa adatokat
   - ✅ Tesztek és validációk

3. **Gyors teszt:**
   - Futtasd a "Login" kérést az admin felhasználóval
   - A token automatikusan mentésre kerül
   - Próbáld ki a "Get All Orders" kérést

## 📚 API Dokumentáció

**Teljes dokumentáció:** [`docs/README.md`](docs/README.md)

### Főbb Endpointok

#### Authentikáció
- `POST /api/register` - Regisztráció
- `POST /api/login` - Bejelentkezés
- `POST /api/logout` - Kijelentkezés
- `POST /api/refresh` - Token frissítés
- `GET /api/me` - Aktuális felhasználó

#### Orders
- `GET /api/orders` - Összes rendelés
- `GET /api/orders/{id}` - Egy rendelés
- `POST /api/orders` - Új rendelés
- `PUT /api/orders/{id}` - Rendelés módosítás
- `DELETE /api/orders/{id}` - Rendelés törlés

#### Payments
- `GET /api/payments` - Összes fizetés
- `GET /api/payments/{id}` - Egy fizetés
- `POST /api/payments` - Új fizetés
- `PUT /api/payments/{id}` - Fizetés módosítás
- `DELETE /api/payments/{id}` - Fizetés törlés

#### Test
- `GET /api/test` - API működés ellenőrzése (nincs auth)

## 🔧 Fejlesztői Parancsok

```bash
# Adatbázis újratöltése teszt adatokkal
php artisan migrate:fresh --seed

# Cache törlése
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Route lista megtekintése
php artisan route:list
```

## 📊 Teszt Adatok

A seederek automatikusan generálnak:
- **1 admin felhasználót**
- **9 normál felhasználót** magyar nevekkel
- **~30 rendelést** véletlenszerű státuszokkal
- **~70-90 fizetést** magyar fizetési módokkal

### Magyar Fizetési Módok
- bankkártya
- készpénz
- átutalás
- PayPal
- Simplepay
- Barion
- utánvét

## 🛡️ Biztonság

- JWT token alapú authentikáció
- Jelszavak hash-elve (bcrypt)
- CORS konfiguráció
- Validációs szabályok minden endpointon
- Jogosultság ellenőrzés minden műveletnél

## 🐛 Gyakori Hibák

### "Token has expired"
```bash
# Frissítsd a tokent a /api/refresh endpointon
# Vagy jelentkezz be újra
```

### "No permission"
```bash
# Ellenőrizd, hogy admin-ként vagy bejelentkezve
# Vagy próbálj saját rendelést/fizetést elérni
```

### Adatbázis újratöltése
```bash
php artisan migrate:fresh --seed
```

## 📁 Projekt Struktúra

```
paymentPlatformJWT/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php
│   │       ├── OrderController.php
│   │       └── PaymentController.php
│   └── Models/
│       ├── User.php
│       ├── Order.php
│       └── Payment.php
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── UserSeeder.php
│       ├── OrderSeeder.php
│       └── PaymentSeeder.php
├── docs/
│   ├── README.md (Teljes dokumentáció)
│   └── Payment_Platform_JWT_API.postman_collection.json
└── routes/
    └── api.php
```

## 📄 Licensz

Ez a projekt oktatási célokra készült.

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
