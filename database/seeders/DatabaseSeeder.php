<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // jalankan seeder role
        $this->call(RoleSeeder::class);

        // pastikan user admin ada
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin'],
            ['name' => 'admin', 'password' => Hash::make('admin')]
        );

        // assign role admin
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
