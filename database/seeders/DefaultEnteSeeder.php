<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\EnteProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultEnteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creazione Ente GoodGo default...');

        // Crea l'utente Ente GoodGo
        $ente = User::firstOrCreate(
            ['email' => 'ente@goodgo.it'],
            [
                'name' => 'GoodGo',
                'password' => Hash::make('password'),
                'type' => UserType::ENTE,
                'email_verified_at' => now(),
            ]
        );

        // Crea o aggiorna il profilo ente
        EnteProfile::updateOrCreate(
            ['user_id' => $ente->id],
            [
                'tipologia' => 'Piattaforma',
                'location' => 'Italia',
                'descrizione' => 'GoodGo è la piattaforma di gamification per la mobilità sostenibile.',
                'iscrizione_moderata' => false,
                'website' => 'https://goodgo.it',
                'colore' => '#4CAF50',
                'is_default' => true,
            ]
        );

        $this->command->info('Ente GoodGo creato!');
        $this->command->info('Email: ente@goodgo.it');
        $this->command->info('Password: password');
    }
}
