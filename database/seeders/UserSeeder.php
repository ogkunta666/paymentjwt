<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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

        // Admin felhasználó létrehozása
        User::create([
            'name' => 'Admin Felhasználó',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'isAdmin' => true,
        ]);

        // 9 normál felhasználó létrehozása magyar nevekkel
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

    /**
     * Remove Hungarian accents from string
     */
    private function removeAccents($string)
    {
        $accents = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ö' => 'o', 'ő' => 'o', 'ú' => 'u', 'ü' => 'u', 'ű' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ö' => 'O', 'Ő' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ű' => 'U'
        ];
        
        return strtr($string, $accents);
    }
}
