<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class SingleUserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'mamyfanojo@gmail.com'],
            [
                'name'     => 'Administrateur',
                'password' => bcrypt('58615861')
            ]
        );
    }
}