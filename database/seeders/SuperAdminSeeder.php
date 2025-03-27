<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'mendrikaarivelo10@gmail.com'],
            [
                'name'          => 'Super Admin',
                'password'      => bcrypt('namy10'),
                'is_super_admin'=> true,
            ]
        );
    }
}

