<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Christy Mears',
            'email' => 'christy@mysticmoonroot.ca',
            'password' => Hash::make('bdbhHY$$e8d35%'),
            'email_verified_at' => now(),
            'theme_mode' => 'light',
            'event_redirect' => 'return_previous'
        ]);

        User::create([
            'name' => 'Moby Hicks',
            'email' => 'info@covenantlabs.ca',
            'password' => Hash::make('e8d35%bdbhHY$$'),
            'email_verified_at' => now(),
            'theme_mode' => 'light',
            'event_redirect' => 'return_previous'
        ]);
    }
}