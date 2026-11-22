<?php
// -shaine: Stores household and economic details for family heads.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyHead extends Model
{
    protected $fillable = [
        'beneficiary_id',
        'household_members',
        'dependents',
        'children',
        'senior_citizens',
        'pwd_members',
        'occupation',
        'source_of_income',
        'monthly_estimated_income',
        'employment_status',
    ];

    // -shaine: Casts household counts and income values to numeric PHP types.
    protected function casts(): array
    {
        return [
            'monthly_estimated_income' => 'decimal:2',
        ];
    }

    // -shaine: Defines the beneficiary whose household details these fields describe.
    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }
}
