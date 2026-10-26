<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'address',
        'barangay_id',
        'sector',
        'household_id',
        'household_members',
        'dependents',
        'children',
        'senior_citizens',
        'pwd_members',
        'occupation',
        'source_of_income',
        'monthly_income',
        'employment_status',
        'id_document_path',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'monthly_income' => 'decimal:2',
        ];
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
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
