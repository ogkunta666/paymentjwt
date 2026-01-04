# Tanulási platform REST API megvalósítása Laravel környezetben

**base_url:** `http://127.0.0.1/learningPlatformBearer/public/api` vagy `http://127.0.0.1:8000/api`

Az API-t olyan funkciókkal kell ellátni, amelyek lehetővé teszik annak nyilvános elérhetőségét. Ennek a backendnek a fő célja, hogy kiszolgálja a frontendet, amelyet a tanulók a kurzusokra való feliratkozásra és a tanulásra használnak.

**Funkciók:**
- Authentikáció (login, token kezelés).
- Felhasználó beiratkozhat egy kurzusra.
- Kurzuson belül a kurzus teljesítését jelöljük.
- A teszteléshez készíts
  - 1 admin (admin / admin)
  - 9 student user (jelszó: Jelszo_2025)
  - 3 releváns kurzus
  - két véletlen student beiratkozásai (köztük 1-1 befejezett)

Az adatbázis neve: `learning_platform.`

## Végpontok:
A `Content-Type` és az `Accept` headerkulcsok mindig `application/json` formátumúak legyenek.

Érvénytelen vagy hiányzó token esetén a backendnek `401 Unauthorized` választ kell visszaadnia:
```json
Response: 401 Unauthorized
{
  "message": "Invalid token"
}
```

### Nem védett végpontok:
- **GET** `/ping` - teszteléshez
- **POST** `/register` - regisztrációhoz
- **POST** `/login` - belépéshez

### Hibák:
- 400 Bad Request: A kérés hibás formátumú. Ezt a hibát akkor kell visszaadni, ha a kérés hibásan van formázva, vagy ha hiányoznak a szükséges mezők.
- 401 Unauthorized: A felhasználó nem jogosult a kérés végrehajtására. Ezt a hibát akkor kell visszaadni, ha az érvénytelen a token.
- 403 Forbidden: A felhasználó nem jogosult a kérés végrehajtására. Ezt a hibát akkor kell visszaadni, ha a kreditkeret túllépésre kerül.
- 404 Not Found: A kért erőforrás nem található. Ezt a hibát akkor kell visszaadni, ha a kért kurzus, alkalom vagy foglalás nem található.
- 503 Service Unavailable: A szolgáltatás nem elérhető. Ezt a hibát akkor kell visszaadni, ha a tanulási szolgáltatás nem elérhető, vagy ha váratlan hibát ad vissza.

---

## Felhasználókezelés


**POST** `/register`

Új felhasználó regisztrálása. Az új felhasználók alapértelmezetten 0 kredittel rendelkeznek. Az e-mail címnek egyedinek kell lennie.

Kérés Törzse:
```JSON
{
    "name": "mozso",
    "email": "mozso@moriczref.hu",
    "password" : "Jelszo_2025",
    "password_confirmation" : "Jelszo_2025"
}
```
Válasz (sikeres regisztráció esetén): `201 Created`
```JSON
{
    "message": "User created successfully",
    "user": {
        "id": 13,
        "name": "mozso",
        "email": "mozso@moriczref.hu",
        "role": "student"
    }
}
```

Automatikus válasz felüldefiniálása (ha az e-mail cím már foglalt): `422 Unprocessable Entity`
```JSON
{
  "message": "Failed to register user",
  "errors": {
    "email": [
      "The email has already been taken."
    ]
  }
}
```
---
**POST** `/login`

Bejelentkezés e-mail címmel és jelszóval.

Kérés Törzse:
```JSON
{
  "email": "mozso@moriczref.hu",
  "password": "Jelszo_2025"
}
```
Válasz (sikeres bejelentkezés esetén): `200 OK`
```JSON
{
    "message": "Login successful",
    "user": {
        "id": 13,
        "name": "mozso",
        "email": "mozso@moriczref.hu",
        "role": "student"
    },
    "access": {
        "token": "2|7Fbr79b5zn8RxMfOqfdzZ31SnGWvgDidjahbdRfL2a98cfd8",
        "token_type": "Bearer"
    }
}
```
Válasz (sikertelen bejelentkezés esetén): 401 Unauthorized
```JSON
{
  "message": "Invalid email or password"
}
```

---
> Az innen következő végpontok autentikáltak, tehát a kérés headerjében meg kell adni a tokent is

> Authorization: "Bearer 2|7Fbr79b5zn8RxMfOqfdzZ31SnGWvgDidjahbdRfL2a98cfd8"                     


**POST** `/logout`

A jelenlegi autentikált felhasználó kijelentkeztetése, a felhasználó tokenjének törlése. Ha a token érvénytelen, a fent meghatározott általános `401 Unauthorized` hibát kell visszaadnia.

Válasz (sikeres kijelentkezés esetén): `200 OK`
```JSON
{
  "message": "Logout successful"
}
```
---
**GET** `/users/me`

Saját felhasználói profil, statisztikák lekérése.

Válasz: `200 OK`
```JSON
{
    "user": {
        "id": 1,
        "name": "admin",
        "email": "admin@example.com",
        "role": "admin"
    },
    "stats": {
        "enrolledCourses": 10,
        "completedCourses": 11
    }
}
```
---
**PUT** `/users/me`

Saját felhasználói adatok frissítése. Az aktuális felhasználó módosíthatja a nevét, e-mail címét és/vagy jelszavát.

Kérés törzse:
```JSON
{
  "name": "Új Név",
  "email": "ujemail@example.com",
  "password": "ÚjJelszo_2025",
  "password_confirmation": "ÚjJelszo_2025"
}
```
Válasz (sikeres frissítés, `200 OK`):
```JSON
{
  "message": "Profile updated successfully",
  "user": {
    "id": 5,
    "name": "Új Név",
    "email": "ujemail@example.com",
    "role": "admin"
  }
}
```
*Hibák:*
`422 Unprocessable Entity` – érvénytelen vagy hiányzó mezők, pl. nem egyezik a password_confirmation, vagy az e-mail már foglalt

`401 Unauthorized` – ha a token érvénytelen vagy hiányzik

---
**GET** `/users`

A felhasználói profilok, statisztikák lekérése az admin számára.

Válasz: `200 OK`
```JSON
{
    "data": [
        {
            "user": {
                "id": 1,
                "name": "admin",
                "email": "admin@example.com",
                "role": "admin"
            },
            "stats": {
                "enrolledCourses": 10,
                "completedCourses": 6
            }
        },
        {
            "user": {
                "id": 2,
                "name": "Aranka Török",
                "email": "atorok@example.com",
                "role": "student"
            },
            "stats": {
                "enrolledCourses": 2,
                "completedCourses": 1
            }
        },
        {
            "user": {
                "id": 3,
                "name": "Barnabás Török",
                "email": "btorok@example.net",
                "role": "student"
            },
            "stats": {
                "enrolledCourses": 2,
                "completedCourses": 1
            }
        }
    ]
}
```
Ha nem admin próbálja elérni a végpontot:

Válasz: `403 Forbidden`
```JSON
{
  "message": "Admin access required"
}
```
---
**GET** `/users/:id`

A felhasználói profil, statisztikák lekérése az admin számára.

Válasz: `200 OK`
```JSON
{
  "user": {
    "id": 5,
    "name": "Eva Rodriguez",
    "email": "eva@example.com",
    "role": "student",
  },
  "stats": {
    "enrolledCourses": 3,
    "completedCourses": 13
  }
}
```
Ha nem admin próbálja elérni a végpontot:

Válasz: `403 Forbidden`
```JSON
{
  "message": "Admin access required"
}
```
Ha törölt (softdeleted) felhasználót próbáltunk megnézni:

Válasz: `404 Not Found`
```JSON
{
  "message": "User not found"
}
```
---
**DELETE** `/users/:id`

Egy felhasználó törlése (Soft Delete) az admin számára.

Ha a felhasználó már törlésre került, vagy nem létezik, a megfelelő hibaüzenetet adja vissza.

Válasz (sikeres törlés esetén): `200 OK`
```JSON
{
  "message": "User deleted successfully"
}
```
Válasz (ha a felhasználó nem található): `404 Not Found`
```JSON
{
  "message": "User not found"
}
```
Válasz (ha a token érvénytelen vagy hiányzik): `401 Unauthorized`
```JSON
{
  "message": "Invalid token"
}
```
---
## Kurzuskezelés:


**GET** `/courses`

Az összes elérhető kurzus listájának lekérése.

Válasz: `200 OK`
```JSON
{
  "courses": [
    {
      "title": "Szoftverfejlesztési alapok",
      "description": "Alapvető programozási fogalmak és minták."
    },
    {
      "title": "REST API fejlesztés",
      "description": "API-k tervezése és készítése Laravelben."
    }
  ]
}
```
---
**GET** `/courses/:id`

Információk lekérése egy adott kurzusról.

Válasz: `200 OK`
```JSON
{
    "course": {
        "title": "Szoftverfejlesztési alapok",
        "description": "Alapvető programozási fogalmak és minták."
    },
    "students": [
        {
            "name": "Barnabás Török",
            "email": "btorok@example.net",
            "completed": false,
        },
        {
            "name": "Andrea Török",
            "email": "atorok@example.net",
            "completed": true,
        }
    ]
}
```
Automatikus válasz (ha a kurzus nem található): `404 Not Found`

---

**POST** `/courses/:id/enroll`

A jelenlegi felhasználó beiratkozása egy kurzusra.

Válasz (sikeres beiratkozás esetén): `200 OK`
```JSON
{
  "message": "Successfully enrolled in course"
}
```
Válasz (ha már beiratkozott): `409 Conflict`
```JSON
{
  "message": "Already enrolled in this course"
}
```
Automatikus válasz (ha a kurzus nem található): `404 Not Found`

---
**PATCH** `/courses/:id/completed`

Jelenlegi felhasználó egy kurzusának befejezettként való megjelölése.

Válasz (sikeres befejezés esetén): `200 OK`
```JSON
{
  "message": "Course completed",
}
```
Válasz (ha nincs beiratkozva): `403 Forbidden`
```JSON
{
  "message": "Not enrolled in this course"
}
```
Válasz (ha már befejezett): `409 Conflict`
```JSON
{
  "message": "Course already completed"
}
```
---
## Összefoglalva

|HTTP metódus|	Útvonal	             |Jogosultság	| Státuszkódok	                                        | Rövid leírás                                 |
|------------|-----------------------|--------------|-------------------------------------------------------|----------------------------------------------|
|GET	     | /ping	             | Nyilvános	| 200 OK	                                            | API teszteléshez                             |
|POST	     | /register	         | Nyilvános	| 201 Created, 400 Bad Request	                        | Új felhasználó regisztrációja                |
|POST	     | /login	             | Nyilvános	| 200 OK, 401 Unauthorized	                            | Bejelentkezés e-maillel és jelszóval         |
|POST	     | /logout	             | Hitelesített | 200 OK, 401 Unauthorized	                            | Kijelentkezés                                |
|GET	     | /users/me	         | Hitelesített | 200 OK, 401 Unauthorized	                            | Saját profil és statisztikák lekérése        |
|PUT         | /users/me	         | Hitelesített | 200 OK, 422 Unprocessable Entity, 401 Unauthorized    | Saját profil adatainak módosítása            |
|GET	     | /users  	             | Admin	    | 200 OK, 403 Forbidden                               	| Összes felhasználó profiljának lekérése      |
|GET	     | /users/:id	         | Admin	    | 200 OK, 403 Forbidden, 404 Not Found, 401 Unauthorized| Bármely felhasználó profiljának lekérése     |
|DELETE	     | /users/:id	         | Admin	    | 200 OK, 404 Not Found, 401 Unauthorized	            | Felhasználó törlése (Soft Delete)            |
|GET	     | /courses	             | Hitelesített | 200 OK, 401 Unauthorized	                            | Kurzusok listázása a beiratkozási státusszal | 
|GET	     | /courses/:id	         | Hitelesített | 200 OK, 404 Not Found, 401 Unauthorized	            | Egy kurzus részletei                         |
|POST	     | /courses/:id/enroll	 | Hitelesített | 200 OK, 409 Conflict, 404 Not Found, 401 Unauthorized	| Beiratkozás kurzusra                         |
|PATCH	     | /courses/:id/completed| Hitelesített | 200 OK, 403 Forbidden, 409 Conflict, 401 Unauthorized	| Kurzus befejezettként jelölése               |


## Adatbázis terv:
```
+---------------------+     +---------------------+       +-----------------+        +------------+
|personal_access_tokens|    |        users        |       |   enrollments   |        |  courses   |
+---------------------+     +---------------------+       +-----------------+        +------------+
| id (PK)             |   _1| id (PK)             |1__    | id (PK)         |     __1| id (PK)    |
| tokenable_id (FK)   |K_/  | name                |   \__N| user_id (FK)    |    /   | title      |
| tokenable_type      |     | email (unique)      |       | course_id (FK)  |M__/    | description|
| name                |     | password            |       | enrolled_at     |        | created_at |
| token (unique)      |     | role (student/admin)|       | completed_at    |        | updated_at |
| abilities           |     | deleted_at          |       +-----------------+        +------------+
| last_used_at        |     +---------------------+
+---------------------+
```


# I. Modul struktúra kialakítása 




## 1. Telepítés (projekt létrehozása, .env konfiguráció, sanctum telepítése, tesztútvonal)


`célhely>composer create-project laravel/laravel --prefer-dist learningPlatformBearer`

`célhely>cd learningPlatformBearer`

*.env fájl módosítása*
```sql
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=learning_platform
    DB_USERNAME=root
    DB_PASSWORD=
```
*config/app.php módosítása*
```php
    'timezone' => 'Europe/Budapest',`
```
`learningPlatformBearer>composer require laravel/sanctum`

`learningPlatformBearer>php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`

`learningPlatformBearer>php artisan install:api`

*api.php:*
```php
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'message' => 'API works!'
    ], 200);
});
```

### Teszt

**serve**

`learningPlatformBearer>php artisan serve`

> POSTMAN teszt: GET http://127.0.0.1:8000/api/ping

*VAGY*

**XAMPP**

> POSTMAN teszt: GET http://127.0.0.1/learningPlatform/public/api/ping

---

## 2. Modellek és migráció (sémák)


Ami már megvan (database/migrations): 

*Ehhez nem is kell nyúlni*
```php
Schema::create('personal_access_tokens', function (Blueprint $table) {
    $table->id();
    $table->morphs('tokenable'); // user kapcsolat
    $table->string('name');
    $table->string('token', 64)->unique();
    $table->text('abilities')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamps();
});
```
*Ezt módosítani kell:*

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    //ezt bele kell írni
    $table->enum('role', ['student', 'admin']);
    //ezt bele kell írni
    $table->softDeletes(); // ez adja hozzá a deleted_at mezőt
    $table->timestamps();
});
```

*app/Models/User.php (módosítani kell)*
```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    //amikor a modellt JSON formátumban adod vissza ne jelenjenek meg a következő mezők:
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments');
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
```


`learningPlatformBearer>php artisan make:model Course -m`

*database/migrations/?_create_courses_table.php (módosítani kell)*
```php
Schema::create('courses', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->timestamps();
});
```

*app/Models/Course.php (módosítani kell)*
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
    ];

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'enrollments');
    }
}
```

`learningPlatformBearer>php artisan make:model Enrollment -m`

*database/migrations/?_create_enrollments_table.php (módosítani kell)*
```php
Schema::create('enrollments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    //a user_id mező a users tábla id oszlopára fog hivatkozni
    $table->foreignId('course_id')->constrained()->cascadeOnDelete();
    $table->timestamp('enrolled_at')->useCurrent();
    $table->timestamp('completed_at')->nullable(); // jelzi, hogy a kurzus befejeződött
});
```

*app/Models/Enrollment.php (módosítani kell)*

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Enrollment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'course_id',
        'enrolled_at',
        'completed_at',
    ];

    protected $dates = [
        'enrolled_at',
        'completed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
```

`learningPlatformBearer>php artisan migrate`

---

## 3. Seeding (Factory és seederek)

*database/factories/UserFactory.php (módosítása)*
```php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        $this->faker = \Faker\Factory::create('hu_HU'); // magyar nevekhez

        return [
            'name' => $this->faker->firstName . ' ' . $this->faker->lastName, // magyaros teljes név
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('Jelszo123'), // minden user jelszava: Jelszo123
            'role' => 'student',
        ];
    }
}
```

`learningPlatformBearer>php artisan make:seeder UserSeeder`

*database/seeders/UserSeeder.php (módosítása)*
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1 admin
        User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin'),
            'role' => 'admin',
        ]);

        // 9 student user
        User::factory(9)->create();
    }
}
```

`learningPlatformBearer>php artisan make:seeder CourseSeeder`

*database/seeders/CourseSeeder.php (módosítása)*
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        Course::create([
            'title' => 'Szoftverfejlesztési alapok',
            'description' => 'Alapvető programozási fogalmak és minták.',
        ]);

        Course::create([
            'title' => 'REST API fejlesztés',
            'description' => 'API-k tervezése és készítése Laravelben.',
        ]);

        Course::create([
            'title' => 'Fullstack webfejlesztés',
            'description' => 'Backend és frontend alapok.',
        ]);
    }
}
```

`learningPlatformBearer>php artisan make:seeder EnrollmentSeeder`

*database/seeders/EnrollmentSeeder.php (módosítása)*
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use Carbon\Carbon;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')->take(2)->get();
        $courses = Course::all();

        // User 1: első két kurzus
        Enrollment::create([
            'user_id' => $students[0]->id,
            'course_id' => $courses[0]->id,
            'enrolled_at' => now(),
            'completed_at' => now(),  // completed
        ]);

        Enrollment::create([
            'user_id' => $students[0]->id,
            'course_id' => $courses[1]->id,
            'enrolled_at' => now(),
            'completed_at' => null,
        ]);

        // User 2: első két kurzus
        Enrollment::create([
            'user_id' => $students[1]->id,
            'course_id' => $courses[0]->id,
            'enrolled_at' => now(),
            'completed_at' => now(), // completed
        ]);

        Enrollment::create([
            'user_id' => $students[1]->id,
            'course_id' => $courses[2]->id,
            'enrolled_at' => now(),
            'completed_at' => null,
        ]);
    }
}
```

*DatabaseSeeder.php (módosítása)*
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CourseSeeder::class,
            EnrollmentSeeder::class,
        ]);
    }
}
```

`learningPlatformBearer>php artisan db:seed`

---

# II. Modul Controller-ek és endpoint-ok


`learningPlatformBearer>php artisan make:controller AuthController`

*app\Http\Controllers\AuthController.php szerkesztése*

```php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|confirmed|min:8',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Failed to register user',
                'errors' => $e->errors() // visszaadja, mely mezők hibásak
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'access' => [
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete(); //minden token törlése
        //$request->user()->currentAccessToken()->delete(); //aktuális token törlése, más eszközökön marad a bejelentkezés 
        return response()->json(['message' => 'Logout successful']);
    }
}
```
`learningPlatformBearer>php artisan make:controller UserController`

*app\Http\Controllers\UserController.php szerkesztése*
```php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * GET /users/me
     * A bejelentkezett felhasználó adatainak lekérése.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'stats' => [
                'enrolledCourses'  => $user->enrollments()->count(),
                'completedCourses' => $user->enrollments()->where('completed_at', true)->count(),
            ]
        ], 200);
    }

    /**
     * PUT /users/me
     * A bejelentkezett felhasználó adatainak frissítése.
     */
    public function updateMe(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'   => 'sometimes|string|max:255',
            'email'  => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|confirmed|min:8',
        ]);

        if ($request->name) {
            $user->name = $request->name;
        }
        if ($request->email) {
            $user->email = $request->email;
        }
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    /**
     * ADMIN ONLY
     * GET /users
     * Összes felhasználó listázása.
     */
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        
        $users = User::all()->map(function ($user) {
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'stats' => [
                'enrolledCourses'  => $user->enrollments()->count(),
                'completedCourses' => $user->enrollments()->whereNotNull('completed_at')->count(),
            ]
        ];
    });

    return response()->json([
        'data' => $users
    ]);

    }

    /**
     * ADMIN ONLY
     * GET /users/{id}
     * Felhasználó lekérése ID alapján.
     */
    public function show(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user = User::withTrashed()->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->trashed()) {
            return response()->json(['message' => 'User is deleted'], 404);
        }

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'stats' => [
                'enrolledCourses'  => $user->enrollments()->count(),
                'completedCourses' => $user->enrollments()->whereNotNull('completed_at')->count(),
            ]
        ]);
    }

    /**
     * ADMIN ONLY
     * DELETE /users/{id}
     * Soft delete felhasználó.
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
```

`learningPlatformBearer>php artisan make:controller CourseController`

*app\Http\Controllers\CourseController.php szerkesztése*
```php
namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        
        $courses = Course::select('title', 'description')->get();

        return response()->json([
            'courses' => $courses
        ]);

    }

    public function show(Course $course)
    {
        // Csak a szükséges mezők a kapcsolt usereknél, valamint a teljesítési státusz
        $students = $course->users()->select('name', 'email')->withPivot('completed_at')->get()->map(function ($user) {
            return [
                'name' => $user->name,
                'email' => $user->email,
                'completed' => !is_null($user->pivot->completed_at)
            ];
        });

        return response()->json([
            'course' => [
                'title' => $course->title,
                'description' => $course->description
            ],
            'students' => $students
        ]);
    }

    public function enroll(Course $course, Request $request)
    {
        $user = $request->user();

        if ($user->courses()->where('course_id', $course->id)->exists()) {
            return response()->json(['message' => 'Already enrolled in this course'], 409);
        }

        $user->courses()->attach($course->id, ['enrolled_at' => now()]);

        return response()->json(['message' => 'Successfully enrolled in course']);
    }

    public function complete(Course $course, Request $request)
    {
        $user = $request->user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment) {
            return response()->json(['message' => 'Not enrolled in this course'], 403);
        }

        if ($enrollment->completed_at) {
            return response()->json(['message' => 'Course already completed'], 409);
        }

        $enrollment->update(['completed_at' => now()]);

        return response()->json(['message' => 'Course completed']);
    }
}
```

*routes\api.php frissítése:*
```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;

// Public
Route::get('/ping', function () { return response()->json(['message'=>'API works!']); });
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/users/me', [UserController::class, 'me']);
    Route::put('/users/me', [UserController::class, 'updateMe']);

    // Admin
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{course}', [CourseController::class, 'show']);
    Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll']);
    Route::patch('/courses/{course}/completed', [CourseController::class, 'complete']);
});
```


# III. Modul Tesztelés 

Feature teszt ideális az HTTP kérések szimulálására, mert több komponens (Controller, Middleware, Auth) együttműködését vizsgáljuk.

`learningPlatformBearer>php artisan make:test AuthTest`

```php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    public function test_ping_endpoint_returns_ok()
    {
        $response = $this->getJson('/api/ping');
        $response->assertStatus(200)
                ->assertJson(['message' => 'API works!']);
    }

    public function test_register_creates_user()
    {
        $payload = [
            'name' => 'Teszt Elek',
            'email' => 'teszt@example.com',
            'password' => 'Jelszo_2025',
            'password_confirmation' => 'Jelszo_2025'
        ];

        $response = $this->postJson('/api/register', $payload);
        $response->assertStatus(201)
                ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email', 'role']]);
        
        // Ellenőrizzük, hogy a felhasználó létrejött az adatbázisban
        $this->assertDatabaseHas('users', [
            'email' => 'teszt@example.com',
        ]);
    }

    public function test_login_with_valid_credentials()
    {
        // ARRANGE: Felhasználó létrehozása az adatbázisban
        // Mivel a regisztrációs teszt csak egyszer fut, létre kell hozni egy felhasználót 
        // minden login teszthez.
        $password = 'Jelszo_2025';
        $user = User::factory()->create([
            'email' => 'validuser@example.com',
            'password' => Hash::make($password), // A jelszót hash-elni kell!
        ]);

        // ACT: Bejelentkezési kérés
        $response = $this->postJson('/api/login', [
            'email' => 'validuser@example.com',
            'password' => $password, // A bejelentkezéshez a plain text jelszót adjuk
        ]);

        // ASSERT: Ellenőrizzük a státuszt és a válasz struktúráját
        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email', 'role'], 'access' => ['token', 'token_type']]);

        // Opcionális: Ellenőrizzük, hogy létrejött-e token
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_login_with_invalid_credentials()
    {
        // ARRANGE: Létrehozzuk a létező felhasználót
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => Hash::make('CorrectPassword'), 
        ]);

        // ACT: Helytelen adatokkal próbálkozunk
        $response = $this->postJson('/api/login', [
            'email' => 'existing@example.com',
            'password' => 'wrongpass' // Helytelen jelszó
        ]);

        // ASSERT: Ellenőrizzük az elutasítást
        // FIGYELEM: Ha a backend 422-t ad vissza validációs hiba (pl. hiányzó mező) helyett, 
        // de az invalid credentials hiba 401, akkor az a helyes.
        $response->assertStatus(401)
                 ->assertJson(['message' => 'Invalid email or password']);
    }

}
```

`learningPlatformBearer>php artisan make:test UserTest`
```php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;

class UserTest extends TestCase
{
    use RefreshDatabase; // Az adatbázis hibák (no such table: users) elkerülésére

    // ----------------------------------------------------------------------------------
    // 1. /users/me (GET) - Lekérés
    // ----------------------------------------------------------------------------------

    public function test_me_endpoint_requires_authentication()
    {
        // A hiba alapján a Laravel alapértelmezett üzenetét várjuk
        $response = $this->getJson('/api/users/me');
        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_me_endpoint_returns_user_data()
    {
        // ARRANGE: Felhasználó létrehozása
        $user = User::factory()->create(['role' => 'student']);
        
        // ACT: Felhasználó hitelesítése a Sanctum-mal
        Sanctum::actingAs($user); 

        // ACT: Kérés küldése
        $response = $this->getJson('/api/users/me');

        // ASSERT: Ellenőrizzük a státuszt és a válasz struktúráját (userController.php alapján)
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'user' => ['id', 'name', 'email', 'role'],
                     'stats' => ['enrolledCourses', 'completedCourses']
                 ])
                 // Ellenőrizzük, hogy a válasz a helyes felhasználót tartalmazza
                 ->assertJsonPath('user.email', $user->email);
    }
    
    // ----------------------------------------------------------------------------------
    // 2. /users/me (PUT) - Profil Frissítés
    // ----------------------------------------------------------------------------------

    public function test_user_can_update_their_own_name_and_email()
    {
        $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);
        Sanctum::actingAs($user); 

        $newEmail = 'new@example.com';
        $newName = 'New Name';

        $response = $this->putJson('/api/users/me', [
            'name' => $newName,
            'email' => $newEmail,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Profile updated successfully'])
                 ->assertJsonPath('user.name', $newName)
                 ->assertJsonPath('user.email', $newEmail);

        // Ellenőrizzük az adatbázist
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $newName,
            'email' => $newEmail,
        ]);
    }
    
    public function test_user_can_update_their_password()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); 

        $newPassword = 'New_Secure_Password_2025';

        $response = $this->putJson('/api/users/me', [
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertStatus(200);

        // Frissítsük a felhasználót az adatbázisból és ellenőrizzük a jelszót
        $updatedUser = User::find($user->id);
        $this->assertTrue(Hash::check($newPassword, $updatedUser->password));
    }
    
    // ----------------------------------------------------------------------------------
    // 3. /users (GET) - Összes felhasználó listázása (Admin Only)
    // ----------------------------------------------------------------------------------

    public function test_student_cannot_access_user_list()
    {
        $student = User::factory()->create(['role' => 'student']);
        Sanctum::actingAs($student); 

        $response = $this->getJson('/api/users');

        // userController.php 120. sor: return response()->json(['message' => 'Forbidden'], 403);
        $response->assertStatus(403)
                 ->assertJson(['message' => 'Forbidden']);
    }

    public function test_admin_can_access_user_list()
    {
        // ARRANGE: Létrehozunk egy admin és néhány student felhasználót
        $admin = User::factory()->create(['role' => 'admin']);
        $students = User::factory(3)->create(['role' => 'student']);
        
        Sanctum::actingAs($admin); 

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => [
                     '*' => [
                         'user' => ['id', 'name', 'email', 'role'],
                         'stats' => ['enrolledCourses', 'completedCourses']
                     ]
                 ]])
                 // Ellenőrizzük, hogy az összes felhasználót (admin + 3 student) visszaadta
                 ->assertJson(fn (AssertableJson $json) =>
                     $json->has('data', 4)
                          ->etc()
                 );
    }
    
    // ----------------------------------------------------------------------------------
    // 4. /users/{id} (GET) - Felhasználó Megtekintése (Admin Only)
    // ----------------------------------------------------------------------------------

    public function test_admin_can_view_specific_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'student', 'name' => 'Target User']);
        
        Sanctum::actingAs($admin); 

        $response = $this->getJson("/api/users/{$targetUser->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('user.name', 'Target User');
    }

    public function test_student_cannot_view_other_users()
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherUser = User::factory()->create(['role' => 'student']);
        
        Sanctum::actingAs($student); 

        $response = $this->getJson("/api/users/{$otherUser->id}");

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Forbidden']);
    }
    
    // ----------------------------------------------------------------------------------
    // 5. /users/{id} (DELETE) - Felhasználó Törlése (Admin Only - Soft Delete)
    // ----------------------------------------------------------------------------------

    public function test_admin_can_soft_delete_a_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $userToDelete = User::factory()->create();
        
        Sanctum::actingAs($admin); 

        $response = $this->deleteJson("/api/users/{$userToDelete->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'User deleted successfully']);

        // Ellenőrizzük, hogy a felhasználó soft deleted
        $this->assertSoftDeleted('users', ['id' => $userToDelete->id]);
    }

    public function test_student_cannot_delete_users()
    {
        $student = User::factory()->create(['role' => 'student']);
        $userToDelete = User::factory()->create();
        
        Sanctum::actingAs($student); 

        $response = $this->deleteJson("/api/users/{$userToDelete->id}");

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Forbidden']);

        // Ellenőrizzük, hogy a felhasználó NEM lett törölve
        $this->assertDatabaseHas('users', ['id' => $userToDelete->id]);
    }
}
```

`learningPlatformBearer>php artisan make:test CourseTest`

```php
namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum; 
use Illuminate\Testing\Fluent\AssertableJson;

class CourseTest extends TestCase
{
    use RefreshDatabase; // Elengedhetetlen az adatbázis táblák létrehozásához

    // ----------------------------------------------------------------------------------
    // 1. /courses (GET) - Lista lekérése
    // ----------------------------------------------------------------------------------

    public function test_course_index_requires_authentication()
    {
        $response = $this->getJson('/api/courses');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_course_index_returns_list_of_courses()
    {
        // ARRANGE: Felhasználó és 3 kurzus manuális létrehozása
        $user = User::factory()->create();
        
        // <<< MANUÁLIS LÉTREHOZÁS FACTORY HELYETT
        Course::create(['title' => 'Kurzus A', 'description' => 'Leírás A']);
        Course::create(['title' => 'Kurzus B', 'description' => 'Leírás B']);
        Course::create(['title' => 'Kurzus C', 'description' => 'Leírás C']);
        
        Sanctum::actingAs($user); 

        // ACT: Kérés küldése
        $response = $this->getJson('/api/courses');

        // ASSERT: Ellenőrizzük a státuszt és a struktúrát
        $response->assertStatus(200)
                 ->assertJsonStructure(['courses' => [
                     '*' => ['title', 'description']
                 ]])
                 ->assertJson(fn (AssertableJson $json) =>
                     $json->has('courses', 3) // Ellenőrizzük, hogy mindhárom kurzus visszajött
                          ->etc()
                 );
    }

    // ----------------------------------------------------------------------------------
    // 2. /courses/{id} (GET) - Kurzus részletek
    // ----------------------------------------------------------------------------------

    public function test_course_show_returns_details_and_students()
    {
        // ARRANGE: Admin és kurzus manuális létrehozása
        $user = User::factory()->create(['role' => 'admin']);
        // <<< MANUÁLIS LÉTREHOZÁS FACTORY HELYETT
        $course = Course::create(['title' => 'Részletes Kurzus', 'description' => 'Részletes Leírás']);
        $student1 = User::factory()->create();
        $student2 = User::factory()->create();

        // Beiratkozás: 1 beiratkozott, 1 befejezett
        $student1->courses()->attach($course->id, ['enrolled_at' => now()]);
        $student2->courses()->attach($course->id, ['enrolled_at' => now(), 'completed_at' => now()]);
        
        Sanctum::actingAs($user); 

        // ACT: Kérés küldése
        $response = $this->getJson("/api/courses/{$course->id}");

        // ASSERT: Ellenőrizzük a státuszt és a fészkelt struktúrát
        $response->assertStatus(200)
                 ->assertJsonPath('course.title', $course->title)
                 ->assertJson(fn (AssertableJson $json) =>
                     $json->has('students', 2) // Két diák van
                          ->where('students.0.completed', false) // student1: nincs completed_at
                          ->where('students.1.completed', true) // student2: van completed_at
                          ->etc()
                 );
    }
    
    // ----------------------------------------------------------------------------------
    // 3. /courses/{id}/enroll (POST) - Beiratkozás
    // ----------------------------------------------------------------------------------

    public function test_user_can_enroll_in_a_course()
    {
        $user = User::factory()->create();
        // <<< MANUÁLIS LÉTREHOZÁS FACTORY HELYETT
        $course = Course::create(['title' => 'Beiratkozó Kurzus', 'description' => 'Leírás']);
        Sanctum::actingAs($user); 

        // ACT: Beiratkozás
        $response = $this->postJson("/api/courses/{$course->id}/enroll");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Successfully enrolled in course']);

        // ASSERT: Ellenőrizzük a kapcsolótáblát
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'completed_at' => null,
        ]);
    }

    public function test_enrollment_fails_if_already_enrolled()
    {
        $user = User::factory()->create();
        // <<< MANUÁLIS LÉTREHOZÁS FACTORY HELYETT
        $course = Course::create(['title' => 'Már Beiratkozott Kurzus', 'description' => 'Leírás']);
        Sanctum::actingAs($user); 
        
        // Először beiratkozunk
        $user->courses()->attach($course->id, ['enrolled_at' => now()]);

        // ACT: Újra megpróbáljuk
        $response = $this->postJson("/api/courses/{$course->id}/enroll");

        $response->assertStatus(409)
                 ->assertJson(['message' => 'Already enrolled in this course']);
    }

    // ----------------------------------------------------------------------------------
    // 4. /courses/{id}/completed (PATCH) - Teljesítés
    // ----------------------------------------------------------------------------------

    public function test_user_can_complete_an_enrolled_course()
    {
        $user = User::factory()->create();
        // <<< MANUÁLIS LÉTREHOZÁS FACTORY HELYETT
        $course = Course::create(['title' => 'Teljesíthető Kurzus', 'description' => 'Leírás']);
        Sanctum::actingAs($user); 
        
        // Beiratkozás
        $user->courses()->attach($course->id, ['enrolled_at' => now()]);

        // ACT: Teljesítés
        $response = $this->patchJson("/api/courses/{$course->id}/completed");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Course completed']);

        // ASSERT: Ellenőrizzük, hogy a completed_at be lett állítva (nem null)
        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'completed_at' => null,
        ]);
    }

    public function test_complete_fails_if_not_enrolled()
    {
        $user = User::factory()->create();
        // <<< MANUÁLIS LÉTREHOZÁS FACTORY HELYETT
        $course = Course::create(['title' => 'Nem Beiratkozott Kurzus', 'description' => 'Leírás']);
        Sanctum::actingAs($user); 
        
        // ACT: Teljesítés beiratkozás nélkül
        $response = $this->patchJson("/api/courses/{$course->id}/completed");

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Not enrolled in this course']);
    }
    
    public function test_complete_fails_if_already_completed()
    {
        $user = User::factory()->create();
        // <<< MANUÁLIS LÉTREHOZÁS FACTORY HELYETT
        $course = Course::create(['title' => 'Már Teljesített Kurzus', 'description' => 'Leírás']);
        Sanctum::actingAs($user); 
        
        // Beiratkozás és teljesítés
        $user->courses()->attach($course->id, ['enrolled_at' => now(), 'completed_at' => now()]);

        // ACT: Újra megpróbáljuk a teljesítést
        $response = $this->patchJson("/api/courses/{$course->id}/completed");

        $response->assertStatus(409)
                 ->assertJson(['message' => 'Course already completed']);
    }
}
```

`learningPlatformBearer>php artisan test`

## Dokumentálás
- word: végpontok
- md: projektleírás/fejlesztői dokumentáció
-scribe
-swagger
-POSTMAN