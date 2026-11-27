<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['period', 'status', 'closed_by', 'closed_at'])]
class AccountingPeriod extends Model
{
    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
        ];
    }
}
