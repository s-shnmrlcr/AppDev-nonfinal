<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = strtolower(trim((string) config('mswd.admin_email', 'mswdo.kabacan@gmail.com')));

        $typoAccount = User::where('email', 'msdwo.kabacan@gmail.com')->first();
        $adminAccount = User::where('email', $adminEmail)->first();

        if ($typoAccount && (! $adminAccount || $typoAccount->is($adminAccount))) {
            $typoAccount->update([
                'name' => 'Admin',
                'email' => $adminEmail,
                'password' => Hash::make('admin123'),
                'role' => 'Admin',
            ]);
        } elseif ($typoAccount) {
            $typoAccount->delete();
        }

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'role' => 'Admin',
            ]
        );

        $this->call([
            BarangaySeeder::class,
        ]);
    }
}
