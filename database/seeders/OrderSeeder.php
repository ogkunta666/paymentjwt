<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('isAdmin', false)->get();
        $statuses = ['pending', 'processing', 'completed', 'cancelled'];

        // Minden felhasználóhoz 2-5 random rendelés
        foreach ($users as $user) {
            $orderCount = rand(2, 5);
            
            for ($i = 0; $i < $orderCount; $i++) {
                Order::create([
                    'user_id' => $user->id,
                    'total_amount' => rand(5000, 150000) / 100, // 50 Ft - 1500 Ft közötti összegek
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => now()->subDays(rand(0, 60)),
                    'updated_at' => now()->subDays(rand(0, 30)),
                ]);
            }
        }

        // Admin felhasználóhoz is adjunk pár rendelést
        $admin = User::where('isAdmin', true)->first();
        if ($admin) {
            for ($i = 0; $i < 3; $i++) {
                Order::create([
                    'user_id' => $admin->id,
                    'total_amount' => rand(10000, 200000) / 100,
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => now()->subDays(rand(0, 60)),
                    'updated_at' => now()->subDays(rand(0, 30)),
                ]);
            }
        }
    }
}
