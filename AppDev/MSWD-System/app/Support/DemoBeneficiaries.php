<?php

namespace App\Support;

class DemoBeneficiaries
{
    public static function all(): array
    {
        return [
            ['id' => 'BEN-00001', 'name' => 'Juan Dela Cruz', 'age' => 36, 'dob' => 'January 15, 1990', 'sex' => 'Male', 'id_number' => '****-****-1234', 'barangay' => 'Barangay 01', 'sector' => 'General Beneficiary', 'dependents' => 3, 'occupation' => 'Driver', 'verification' => 'Verified', 'payout' => 'Ready for Release'],
            ['id' => 'BEN-00002', 'name' => 'Maria Santos', 'age' => 42, 'dob' => 'March 22, 1984', 'sex' => 'Female', 'id_number' => '****-****-2345', 'barangay' => 'Barangay 02', 'sector' => 'Solo Parent', 'dependents' => 4, 'occupation' => 'Vendor', 'verification' => 'Pending', 'payout' => 'Pending'],
            ['id' => 'BEN-00003', 'name' => 'Pedro Garcia', 'age' => 58, 'dob' => 'July 9, 1968', 'sex' => 'Male', 'id_number' => '****-****-3456', 'barangay' => 'Barangay 03', 'sector' => 'General Beneficiary', 'dependents' => 2, 'occupation' => 'Farmer', 'verification' => 'Verified', 'payout' => 'Released'],
            ['id' => 'BEN-00004', 'name' => 'Ana Reyes', 'age' => 67, 'dob' => 'November 2, 1958', 'sex' => 'Female', 'id_number' => '****-****-4567', 'barangay' => 'Barangay 04', 'sector' => 'Senior Citizen', 'dependents' => 1, 'occupation' => 'Retired', 'verification' => 'Rejected', 'payout' => 'Blocked'],
            ['id' => 'BEN-00005', 'name' => 'Liza Mendoza', 'age' => 31, 'dob' => 'May 14, 1995', 'sex' => 'Female', 'id_number' => '****-****-5678', 'barangay' => 'Barangay 05', 'sector' => 'Pregnant/Lactating', 'dependents' => 2, 'occupation' => 'Self-employed', 'verification' => 'Verified', 'payout' => 'Ready for Release'],
            ['id' => 'BEN-00006', 'name' => 'Roberto Lim', 'age' => 45, 'dob' => 'February 11, 1981', 'sex' => 'Male', 'id_number' => '****-****-6789', 'barangay' => 'Barangay 06', 'sector' => 'PWD', 'dependents' => 2, 'occupation' => 'Repair Technician', 'verification' => 'Pending', 'payout' => 'Pending'],
            ['id' => 'BEN-00007', 'name' => 'Carla Navarro', 'age' => 29, 'dob' => 'August 30, 1997', 'sex' => 'Female', 'id_number' => '****-****-7890', 'barangay' => 'Barangay 07', 'sector' => 'General Beneficiary', 'dependents' => 1, 'occupation' => 'Laundry Worker', 'verification' => 'Verified', 'payout' => 'Released'],
            ['id' => 'BEN-00008', 'name' => 'Daniel Flores', 'age' => 52, 'dob' => 'December 5, 1973', 'sex' => 'Male', 'id_number' => '****-****-8901', 'barangay' => 'Barangay 08', 'sector' => 'Indigenous People', 'dependents' => 5, 'occupation' => 'Farmer', 'verification' => 'Verified', 'payout' => 'Already Received'],
        ];
    }

    public static function find(string $id): ?array
    {
        foreach (self::all() as $beneficiary) {
            if ($beneficiary['id'] === $id) {
                return $beneficiary;
            }
        }

        return null;
    }
}
