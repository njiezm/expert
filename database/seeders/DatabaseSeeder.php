<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Compte unique. Le mot de passe est modifiable via MERIDIEN_PASSWORD
        // dans .env avant le premier lancement.
        User::updateOrCreate(
            ['email' => env('MERIDIEN_EMAIL', 'njiezamon10@gmail.com')],
            [
                'name' => 'Njie Zamon',
                'password' => Hash::make(env('MERIDIEN_PASSWORD', 'NjieZm190964@')),
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            SubjectSeeder::class,
            ChapterSeeder::class,
            GapSeeder::class,
            RigueurContentSeeder::class,
            AloSocleSeeder::class,
            CoursAloSeeder::class,
            AloContentSeeder::class,
            AloExercicesSeeder::class,
            SppContentSeeder::class,
            SppContentSeeder2::class,
            CoursSppSeeder::class,
            MiaSocleSeeder::class,
            CoursMiaSeeder::class,
            CoursMia2Seeder::class,
            MiaContentSeeder::class,
            CoursAgcSeeder::class,
            CoursAgc2Seeder::class,
            AgcContentSeeder::class,
            EpContentSeeder::class,
            AgcEpSocleSeeder::class,
            DiagnosticApprofondiSeeder::class,
            DiagnosticApprofondi2Seeder::class,
            SecondsExamensBlancsSeeder::class,
            ExercicesPoidsFortSeeder::class,
            ExercicesAgcSppSeeder::class,
            ExercicesRestantsSeeder::class,
            ActiverSchemasSeeder::class,
        ]);
    }
}