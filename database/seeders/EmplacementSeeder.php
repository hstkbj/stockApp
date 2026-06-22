<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Emplacement;

class EmplacementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([Emplacement::BOUTIQUE, Emplacement::MAGASIN] as $nom) {
            Emplacement::firstOrCreate(['nom' => $nom]);
        }
    }
}
