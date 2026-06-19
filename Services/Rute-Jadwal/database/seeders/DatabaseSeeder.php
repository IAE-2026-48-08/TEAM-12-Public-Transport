<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        Schedule::create([
            'route' => 'Bandung (Leuwi Panjang) - Jakarta (Kampung Rambutan)',
            'departure_time' => '2026-06-05 08:00:00',
            'facilities' => 'AC, WiFi, Colokan Listrik',
            'price' => 120000,
        ]);

        Schedule::create([
            'route' => 'Bandung (Cicaheum) - Surabaya (Bungurasih)',
            'departure_time' => '2026-06-05 15:30:00',
            'facilities' => 'AC, TV, Toilet, Makan Malam',
            'price' => 250000,
        ]);

        Schedule::create([
            'route' => 'Jakarta (Lebak Bulus) - Yogyakarta (Giwangan)',
            'departure_time' => '2026-06-05 18:00:00',
            'facilities' => 'AC, Reclining Seat, Snack',
            'price' => 180000,
        ]);
    }
}