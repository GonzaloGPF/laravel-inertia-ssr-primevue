<?php

namespace App\Traits;

use App\Interfaces\HasCreatedByInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait HasCreatedBy
 */
trait HasCreatedBy
{
    public function initializeHasCreatedBy(): void
    {
        $this->casts += ['created_by' => 'int'];
    }

    public static function bootHasCreatedBy(): void
    {
        static::creating(function (HasCreatedByInterface $model) {
            $model->created_by = $model->created_by ?? auth()->id();
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
