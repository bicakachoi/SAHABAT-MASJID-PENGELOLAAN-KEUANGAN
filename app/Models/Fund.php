<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'restriction', 'is_active'])]
class Fund extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function isRestricted(): bool
    {
        return $this->restriction === 'restricted';
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
