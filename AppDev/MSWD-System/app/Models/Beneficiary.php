<?php
// -shaine: Represents beneficiary identity, eligibility, and review data.

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
        'review_remarks',
    ];

    // -shaine: Casts date and family-head database values to their PHP types.
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_familyhead' => 'boolean',
        ];
    }


    // -shaine: Defines the barangay that owns this beneficiary record.
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    // -shaine: Defines the optional household details for this family head.
    public function familyHead(): HasOne
    {
        return $this->hasOne(FamilyHead::class);
    }

    // -shaine: Joins non-empty name parts into the beneficiary's display name.
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
