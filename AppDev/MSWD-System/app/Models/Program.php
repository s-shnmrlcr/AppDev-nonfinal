<?php
// -shaine: Represents an assistance program and its payout assignments.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = [
        'name',
        'type',
        'amount',
        'coverage',
        'program_name',
        'description',
        'budget',
        'start_date',
        'end_date',
        'status',
    ];

    // -shaine: Casts monetary budget and program dates to consistent model types.
    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    // -shaine: Defines every beneficiary payout assigned to this program.
    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }
}
