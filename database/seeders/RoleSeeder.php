<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['Admin', 'Superadmin', 'Gerant', 'Caisse', 'Accueil'];
        $pages = config('app_pages', []);

        foreach ($roles as $roleName) {
            $roleId = DB::table('roles')->insertGetId([
                'name'       => $roleName,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Accès complet automatique pour Admin & Superadmin
            $fullAccess = in_array($roleName, ['Admin', 'Superadmin']);

            foreach ($pages as $routeName => $label) {
                DB::table('permissions')->insert([
                    'role_id'     => $roleId,
                    'route_name'  => $routeName,
                    'create'      => $fullAccess,
                    'read'        => $fullAccess,
                    'update'      => $fullAccess,
                    'delete'      => $fullAccess,
                    'access_page' => $fullAccess,
                    'created_at'  => Carbon::now(),
                    'updated_at'  => Carbon::now(),
                ]);
            }
        }
    }
}
