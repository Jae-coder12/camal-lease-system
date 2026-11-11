<?php
namespace Database\Seeders;

use App\Models\Stall;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'System Admin', 'password' => 'password', 'role' => 'admin',
        ]);

        foreach (range(1, 8) as $i) {
            Stall::firstOrCreate(['stall_number' => sprintf('S-%03d', $i)], [
                'location' => $i <= 4 ? 'Ground Floor' : 'Second Floor',
                'size_sqm' => 10 + $i,
                'monthly_rate' => 2500 + ($i * 250),
                'status' => 'available',
            ]);
        }
    }
}
