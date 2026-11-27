<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['transaction_id', 'entry_date', 'fund_id', 'accounting_period_id', 'memo'])]
class JournalEntry extends Model
{
    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }
}
