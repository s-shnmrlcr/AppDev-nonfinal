<?php
// -shaine: Represents an assigned beneficiary payout under a program.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Payout extends Model
{
    protected $fillable = [
        'reference',
        'beneficiary_id',
        'program_id',
        'amount',
        'status',
        'released_at',
    ];

    // -shaine: Casts payout amounts and release timestamps for reliable comparisons.
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'released_at' => 'datetime',
        ];
    }

    // -shaine: Defines the beneficiary receiving this payout.
    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    // -shaine: Defines the assistance program funding this payout.
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    // -shaine: Defines the partial or complete releases made against this payout.
    public function releases(): HasMany
    {
        return $this->hasMany(PayoutRelease::class);
    }
}
