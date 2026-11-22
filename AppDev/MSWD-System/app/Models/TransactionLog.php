<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class TransactionLog extends Model
{
    protected $fillable = [
        'user_id',
        'staff_name',
        'action',
        'subject_type',
        'subject_id',
        'details',
    ];

    public static function record(
        string $staffName,
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?string $details = null
    ): self {
        return static::create([
            'user_id' => Auth::id(),
            'staff_name' => trim($staffName),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'details' => $details,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
