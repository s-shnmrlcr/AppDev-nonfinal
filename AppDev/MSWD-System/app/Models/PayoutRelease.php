<?php
// -shaine: Represents a recorded payment release for a payout.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutRelease extends Model
{
    protected $fillable = [
        'payout_id',
        'reference',
        'amount',
        'released_at',
        'released_by',
        'status',
    ];

    // -shaine: Casts release amounts and release timestamps to usable PHP types.
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'released_at' => 'datetime',
        ];
    }

    // -shaine: Defines the payout record this release belongs to.
    public function payout(): BelongsTo
    {
        return $this->belongsTo(Payout::class);
    }

    // -shaine: Defines the account associated with the release record.
    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

}
