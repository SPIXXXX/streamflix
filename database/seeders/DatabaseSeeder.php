<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'client']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@streamflix.test'],
            ['name' => 'Admin', 'password' => bcrypt('admin123')]
        );
        $admin->assignRole('admin');

        $client = User::firstOrCreate(
            ['email' => 'client@streamflix.test'],
            ['name' => 'Client', 'password' => bcrypt('client123')]
        );
        $client->assignRole('client');
    }
}