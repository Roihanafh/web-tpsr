<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ====== CREATE TEST USERS & ASSIGN ROLES ======
        $guru = User::factory()->create([
            'name' => 'Guru TPSR',
            'email' => 'guru@example.com',
        ]);
        $guru->assignRole('guru');

        $admin = User::factory()->create([
            'name' => 'Admin System',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin');
    }
}
