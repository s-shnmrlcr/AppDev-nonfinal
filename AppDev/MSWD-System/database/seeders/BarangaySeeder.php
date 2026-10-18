<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barangays = [
            'Aringay',
            'Bangilan',
            'Bannawag',
            'Cuyapon',
            'Dagupan',
            'Katidtuan',
            'Kayaga',
            'Kilagasan',
            'Magatos',
            'Malamote',
            'Malanduague',
            'Osias',
            'Paatan Lower',
            'Paatan Upper',
            'Pisan',
            'Poblacion',
            'Salapungan',
        ];

        foreach ($barangays as $name) {
            DB::table('barangays')->updateOrInsert(
                ['name' => $name],
                ['name' => $name]
            );
        }
    }
}
