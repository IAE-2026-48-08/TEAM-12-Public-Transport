<?php

namespace Database\Seeders;

use App\Models\User;
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

        for ($i = 0; $i < 5; $i++) {
            \App\Models\Ticket::create([
                'schedule_id' => (string)(102022400251 + $i),
                'seat_number' => 'A' . ($i + 1),
                'total_price' => 100000 + ($i * 15000),
                'status' => 'LUNAS',
            ]);
        }
    }
}
