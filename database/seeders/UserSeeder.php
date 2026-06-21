<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'full_name' => 'Superadmin',
                'email' => 'superadmin@gmail.com',
                'role' => 'Superadmin',
                'password' => 'stockSuper@2025',
            ],
            [
                'full_name' => 'Admin',
                'email' => 'admin@gmail.com',
                'role' => 'Admin',
                'password' => 'stockAdmin@2025',
            ],
            [
                'full_name' => 'Gerant',
                'email' => 'gerant@gmail.com',
                'role' => 'Gerant',
                'password' => 'stockGerant@2025',
            ],
            [
                'full_name' => 'Caisse',
                'email' => 'caisse@gmail.com',
                'role' => 'Caisse',
                'password' => 'stockCaisse@2025',
            ],
            [
                'full_name' => 'Accueil',
                'email' => 'accueil@gmail.com',
                'role' => 'Accueil',
                'password' => 'stockAccueil@2025',
            ],
        ];

        foreach ($users as $user) {
            $roleId = DB::table('roles')->where('name', $user['role'])->value('id');

            DB::table('users')->insert([
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'password' => Hash::make($user['password']), // personnalisé
                'role_id' => $roleId,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }

}
