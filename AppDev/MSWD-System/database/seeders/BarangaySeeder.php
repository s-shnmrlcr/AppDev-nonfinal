<?php
// -shaine: Seeds the standard barangay records used by the portal.

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangaySeeder extends Seeder
{
    /** -shaine: Inserts or updates the official barangay list without duplicates. */
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
            // Update-or-insert aron dili modoble ang barangay kung balik-balikon ang seeding. -shaine
            DB::table('barangays')->updateOrInsert(
                ['name' => $name],
                ['name' => $name]
            );
        }
    }
}
