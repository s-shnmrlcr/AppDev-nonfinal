<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Beneficiary extends Model
{
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'date_of_birth',
        'sex',
        'civil_status',
        'contact_number',
        'purok',
        'barangay_id',
        'sector',
        'is_familyhead',
        'verification_status',
        'id_document_path',
    ];

    // gi change ko ang monthly income na field kay didto ko siya ibutang sa family heads table --shijme
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_familyhead' => 'boolean',
        ];
    }


    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    // mag check if ang beneficiary is also a family head --shijme
    public function familyHead(): HasOne
    {
        return $this->hasOne(FamilyHead::class);
    }

    public function getFullNameAttribute(): string
    {
        // I-filter ang wala sulod nga middle name o suffix aron walay sobra nga espasyo sa full name. -shaine
        return implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ]));
    }
}
