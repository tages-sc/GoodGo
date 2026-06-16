<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creazione Super Admin...');

        $user = User::firstOrCreate(
            ['email' => 'admin@goodgo.it'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('4%1QDHKOh8l*TqaI'),
                'type' => UserType::SUPER_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        // Iscrivi all'ente GoodGo di default
        $user->joinDefaultEnte();

        $this->command->info('Super Admin creato!');
        $this->command->info('Email: admin@goodgo.it');
        $this->command->info('Password: 4%1QDHKOh8l*TqaI');
    }
}
