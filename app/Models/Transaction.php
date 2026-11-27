<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'trx_number', 'trx_date', 'type', 'fund_id', 'category_id',
    'cash_account_id', 'to_cash_account_id', 'amount', 'description',
    'counterparty', 'status', 'void_reason', 'created_by', 'approved_by', 'row_hash',
])]
class Transaction extends Model
{
    protected function casts(): array
    {
        return [
            'trx_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function fund(): BelongsTo
    {
        return $this->belongsTo(Fund::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function toCashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class, 'to_cash_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journalEntry(): HasOne
    {
        return $this->hasOne(JournalEntry::class);
    }
}
