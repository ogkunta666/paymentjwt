<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
                // Az utolsó fizetés kapja a maradék összeget
                if ($i == $paymentCount - 1) {
                    $amount = $remainingAmount;
                } else {
                    // Random részösszeg, de max a maradék 70%-a
                    $maxAmount = $remainingAmount * 0.7;
                    $amount = rand(100, (int)($maxAmount * 100)) / 100;
                    $remainingAmount -= $amount;
                }

                // Completed rendeléseknél biztosan legyen paid_at
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
