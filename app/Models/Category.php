<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'type', 'account_id', 'default_fund_id', 'icon'])]
class Category extends Model
{
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function defaultFund(): BelongsTo
    {
        return $this->belongsTo(Fund::class, 'default_fund_id');
    }
}
