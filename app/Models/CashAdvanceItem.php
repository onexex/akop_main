<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashAdvanceItem extends Model
{
    protected $fillable = [
        'cash_advance_id',
        'details',
        'qty',
        'amount_usd',
        'amount_peso',
        'total'
    ];

    // Boot function para sa auto-computation ng total
    protected static function booted()
    {
        static::saving(function ($item) {
            $item->total = $item->qty * $item->amount_peso;
        });
    }

    public function cashAdvance(): BelongsTo
    {
        return $this->belongsTo(CashAdvance::class);
    }
}
