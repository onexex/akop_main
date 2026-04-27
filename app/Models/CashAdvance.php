<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashAdvance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ca_number',
        'user_id',
        'date',
        'district_office',
        'purpose',
        'beneficiaries',
        'amount_in_figure',
        'amount_in_words',
        'status',
        'first_approver_id',
        'first_approved_at',
        'second_approver_id',
        'second_approved_at',
        'rejected_by',
        'rejection_reason'
    ];

    protected $casts = [
        'date' => 'date',
        'amount_in_figure' => 'decimal:2',
    ];

    // Relationship sa mga items (One-to-Many)
    public function items(): HasMany
    {
        return $this->hasMany(CashAdvanceItem::class);
    }

    // Ang owner ng Cash Advance
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Approver relationships
    public function firstApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'first_approver_id');
    }

    public function secondApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'second_approver_id');
    }
}